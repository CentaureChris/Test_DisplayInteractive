<?php

declare(strict_types=1);

namespace App\Service\Import;

use App\Entity\Customer;
use App\Entity\Purchase;
use App\Repository\CustomerRepository;
use App\Repository\PurchaseRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class ImportPurchasesService
{
    private const BATCH_SIZE = 100;

    public function __construct(
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
        private readonly CustomerRepository $customerRepository,
        private readonly PurchaseRepository $purchaseRepository,
    ) {}

    public function import(string $sqlPath, string $customersPath, string $purchasesPath, SymfonyStyle $io): ImportPurchasesResult
    {
        $this->assertReadableFile($sqlPath, 'SQL');
        $this->assertReadableFile($customersPath, 'customers CSV');
        $this->assertReadableFile($purchasesPath, 'purchases CSV');

        $io->section('Initialize database schema');
        $this->initializeDatabaseFromSql($sqlPath);

        $io->section('Import customers');
        [$createdCustomers, $updatedCustomers] = $this->importCustomers($customersPath);

        $io->section('Import purchases');
        [$createdPurchases, $updatedPurchases] = $this->importPurchases($purchasesPath);

        return new ImportPurchasesResult(
            createdCustomers: $createdCustomers,
            updatedCustomers: $updatedCustomers,
            createdPurchases: $createdPurchases,
            updatedPurchases: $updatedPurchases,
        );
    }

    private function initializeDatabaseFromSql(string $sqlPath): void
    {
        $content = file_get_contents($sqlPath);
        if ($content === false) {
            throw new RuntimeException(sprintf('Cannot read SQL file "%s".', $sqlPath));
        }

        $statements = $this->splitSqlStatements($content);
        if ($statements === []) {
            throw new RuntimeException('SQL file does not contain executable statements.');
        }

        $foreignKeyChecksDisabled = false;
        try {
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
            $foreignKeyChecksDisabled = true;
            $this->connection->executeStatement('DROP TABLE IF EXISTS purchases');
            $this->connection->executeStatement('DROP TABLE IF EXISTS customers');

            foreach ($statements as $statement) {
                $this->connection->executeStatement($statement);
            }

            $this->ensureSchemaCompatibility();
        } catch (Throwable $exception) {
            throw new RuntimeException('Database initialization failed: ' . $exception->getMessage(), 0, $exception);
        } finally {
            if ($foreignKeyChecksDisabled) {
                try {
                    $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
                } catch (Throwable) {
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $withoutConditionalComments = preg_replace('/\/\*![\s\S]*?\*\//', '', $sql) ?? $sql;
        $lines = preg_split('/\R/', $withoutConditionalComments) ?: [];

        $cleanedLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if (str_starts_with($trimmed, '--')) {
                continue;
            }

            if (str_starts_with($trimmed, '/*') && str_ends_with($trimmed, '*/')) {
                continue;
            }

            $cleanedLines[] = $line;
        }

        $joined = implode("\n", $cleanedLines);
        $statements = preg_split('/;\s*(?:\R|$)/', $joined) ?: [];

        $result = [];
        foreach ($statements as $statement) {
            $trimmed = trim($statement);
            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^(START TRANSACTION|COMMIT)$/i', $trimmed) === 1) {
                continue;
            }

            $result[] = $trimmed;
        }

        return $result;
    }

    private function ensureSchemaCompatibility(): void
    {
        $this->connection->executeStatement(
            'ALTER TABLE customers '
                . 'MODIFY title TINYINT NULL, '
                . 'MODIFY lastname VARCHAR(255) NULL, '
                . 'MODIFY firstname VARCHAR(255) NULL, '
                . 'MODIFY postal_code INT NULL, '
                . 'MODIFY city VARCHAR(255) NULL, '
                . 'MODIFY email VARCHAR(255) NULL'
        );

        $this->connection->executeStatement('ALTER TABLE purchases MODIFY price DECIMAL(10,2) NOT NULL');
    }

    /**
     * @return array{int, int}
     */
    private function importCustomers(string $customersPath): array
    {
        $created = 0;
        $updated = 0;
        $processed = 0;

        foreach (
            $this->iterateCsv(
                $customersPath,
                ['customer_id', 'title', 'lastname', 'firstname', 'postal_code', 'city', 'email'],
            ) as [$lineNumber, $row]
        ) {
            if (
                empty($row['title'])
                && empty($row['lastname'])
                && empty($row['firstname'])
                && empty($row['postal_code'])
                && empty($row['city'])
                && empty($row['email'])
            ) {
                continue;
            }
            $customerId = $this->parseRequiredInt($row['customer_id'], 'customer_id', $lineNumber);
            $customer = $this->customerRepository->find($customerId);

            if ($customer === null) {
                $customer = new Customer($customerId);
                ++$created;
            } else {
                ++$updated;
            }

            $customer
                ->setTitle($this->parseNullableInt($row['title'], 'title', $lineNumber))
                ->setLastname($this->normalizeNullableString($row['lastname']))
                ->setFirstname($this->normalizeNullableString($row['firstname']))
                ->setPostalCode($this->parseNullableInt($row['postal_code'], 'postal_code', $lineNumber))
                ->setCity($this->normalizeNullableString($row['city']))
                ->setEmail($this->normalizeNullableString($row['email']));

            $this->entityManager->persist($customer);
            ++$processed;

            if ($processed % self::BATCH_SIZE === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return [$created, $updated];
    }

    /**
     * @return array{int, int}
     */
    private function importPurchases(string $purchasesPath): array
    {
        $created = 0;
        $updated = 0;
        $processed = 0;

        foreach (
            $this->iterateCsv(
                $purchasesPath,
                ['purchase_identifier', 'customer_id', 'product_id', 'quantity', 'price', 'currency', 'date'],
            ) as [$lineNumber, $row]
        ) {
            $identifierId = $this->normalizeRequiredString($row['purchase_identifier'], 'purchase_identifier', $lineNumber);
            $customerId = $this->parseRequiredInt($row['customer_id'], 'customer_id', $lineNumber);
            $customer = $this->customerRepository->find($customerId);

            if ($customer === null) {
                throw new RuntimeException(sprintf('Unknown customer_id "%d" on line %d.', $customerId, $lineNumber));
            }

            $purchase = $this->purchaseRepository->findOneByIdentifierId($identifierId);

            if ($purchase === null) {
                $purchase = (new Purchase())->setPurchaseIdentifier($identifierId);
                ++$created;
            } else {
                ++$updated;
            }

            $purchase
                ->setCustomer($customer)
                ->setProductId($this->parseRequiredInt($row['product_id'], 'product_id', $lineNumber))
                ->setQuantity($this->parseRequiredInt($row['quantity'], 'quantity', $lineNumber))
                ->setPrice($this->parseRequiredFloat($row['price'], 'price', $lineNumber))
                ->setCurrency(strtoupper($this->normalizeRequiredString($row['currency'], 'currency', $lineNumber)))
                ->setDate($this->parseRequiredDate($row['date'], $lineNumber));

            $this->entityManager->persist($purchase);
            ++$processed;

            if ($processed % self::BATCH_SIZE === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return [$created, $updated];
    }

    /**
     * @param list<string> $requiredHeaders
     *
     * @return \Generator<int, array{int, array<string, string>}>
     */
    private function iterateCsv(string $path, array $requiredHeaders): \Generator
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException(sprintf('Cannot open CSV file "%s".', $path));
        }

        try {
            $headers = fgetcsv($handle, 0, ';');
            if ($headers === false) {
                throw new RuntimeException(sprintf('CSV file "%s" is empty.', $path));
            }

            $normalizedHeaders = array_map(
                fn(?string $header): string => $this->normalizeHeader((string) $header),
                $headers,
            );

            $missingHeaders = array_values(array_diff($requiredHeaders, $normalizedHeaders));
            if ($missingHeaders !== []) {
                throw new RuntimeException(sprintf(
                    'CSV file "%s" is missing header(s): %s.',
                    $path,
                    implode(', ', $missingHeaders),
                ));
            }

            /** @var array<string, int> $headerIndexes */
            $headerIndexes = array_flip($normalizedHeaders);
            $lineNumber = 1;

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                ++$lineNumber;

                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $record = [];
                foreach ($requiredHeaders as $headerName) {
                    $index = $headerIndexes[$headerName];
                    $record[$headerName] = $this->cleanCsvValue($row[$index] ?? '');
                }

                yield [$lineNumber, $record];
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param list<string|null> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->cleanCsvValue($value ?? '') !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = trim($header);
        $normalized = preg_replace('/^\xEF\xBB\xBF/', '', $normalized) ?? $normalized;

        return strtolower($normalized);
    }

    private function cleanCsvValue(string $value): string
    {
        return trim($value, " \t\n\r\0\x0B\"");
    }

    private function normalizeNullableString(string $value): ?string
    {
        $cleaned = trim($value);

        return $cleaned === '' ? null : $cleaned;
    }

    private function normalizeRequiredString(string $value, string $column, int $lineNumber): string
    {
        $cleaned = trim($value);
        if ($cleaned === '') {
            throw new RuntimeException(sprintf('Missing "%s" value on line %d.', $column, $lineNumber));
        }

        return $cleaned;
    }

    private function parseRequiredInt(string $value, string $column, int $lineNumber): int
    {
        $parsed = $this->parseNullableInt($value, $column, $lineNumber);
        if ($parsed === null) {
            throw new RuntimeException(sprintf('Missing "%s" integer on line %d.', $column, $lineNumber));
        }

        return $parsed;
    }

    private function parseNullableInt(string $value, string $column, int $lineNumber): ?int
    {
        $cleaned = trim($value);
        if ($cleaned === '') {
            return null;
        }

        if (preg_match('/^-?\d+$/', $cleaned) !== 1) {
            throw new RuntimeException(sprintf('Invalid integer for "%s" on line %d.', $column, $lineNumber));
        }

        return (int) $cleaned;
    }

    private function parseRequiredFloat(string $value, string $column, int $lineNumber): float
    {
        $cleaned = trim(str_replace(',', '.', $value));
        if ($cleaned === '') {
            throw new RuntimeException(sprintf('Missing "%s" decimal on line %d.', $column, $lineNumber));
        }

        if (!is_numeric($cleaned)) {
            throw new RuntimeException(sprintf('Invalid decimal for "%s" on line %d.', $column, $lineNumber));
        }

        return (float) $cleaned;
    }

    private function parseRequiredDate(string $value, int $lineNumber): DateTimeImmutable
    {
        $cleaned = trim($value);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $cleaned);

        if ($date === false) {
            throw new RuntimeException(sprintf('Invalid date "%s" on line %d. Expected format Y-m-d.', $cleaned, $lineNumber));
        }

        return $date;
    }

    private function assertReadableFile(string $path, string $label): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException(sprintf('%s file "%s" is not readable.', $label, $path));
        }
    }
}
