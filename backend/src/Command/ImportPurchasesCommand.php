<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Import\ImportPurchasesService;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

#[AsCommand(
    name: 'ugo:orders:import',
    description: 'Initialize the database from SQL file and import datas using customers/purchases CSV files.',
)]
final class ImportPurchasesCommand extends Command
{
    public function __construct(
        private readonly ImportPurchasesService $importPurchasesService,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('sql-path', null, InputOption::VALUE_OPTIONAL, 'Path to the SQL schema file.')
            ->addOption('customers-path', null, InputOption::VALUE_OPTIONAL, 'Path to the customers CSV file.')
            ->addOption('purchase-path', null, InputOption::VALUE_OPTIONAL, 'Path to the purchases CSV file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $sqlPath = $this->resolveImportPath(
                $input->getOption('sql-path'),
                [
                    $this->projectDir . '/var/import/customers_orders.sql',
                    $this->projectDir . '/../customers_orders.sql',
                ],
                'SQL',
            );

            $customersPath = $this->resolveImportPath(
                $input->getOption('customers-path'),
                [
                    $this->projectDir . '/var/import/customers.csv',
                    $this->projectDir . '/../customers.csv',
                ],
                'customers CSV',
            );

            $purchasesPath = $this->resolveImportPath(
                $input->getOption('purchase-path'),
                [
                    $this->projectDir . '/var/import/purchase.csv',
                    $this->projectDir . '/var/import/purchases.csv',
                    $this->projectDir . '/../purchase.csv',
                    $this->projectDir . '/../purchases.csv',
                ],
                'purchases CSV',
            );

            $io->text([
                sprintf('SQL: %s', $sqlPath),
                sprintf('Customers: %s', $customersPath),
                sprintf('Purchases: %s', $purchasesPath),
            ]);

            $result = $this->importPurchasesService->import($sqlPath, $customersPath, $purchasesPath, $io);

            $io->success('Import completed successfully.');
            $io->table(
                ['Entity', 'Created', 'Updated'],
                [
                    ['Customers', (string) $result->createdCustomers, (string) $result->updatedCustomers],
                    ['Purchases', (string) $result->createdPurchases, (string) $result->updatedPurchases],
                ],
            );

            return Command::SUCCESS;
        } catch (Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * @param list<string> $defaultCandidates
     */
    private function resolveImportPath(mixed $providedPath, array $defaultCandidates, string $label): string
    {
        if (is_string($providedPath) && trim($providedPath) !== '') {
            $candidate = $this->toAbsolutePath(trim($providedPath));
            if (is_file($candidate) && is_readable($candidate)) {
                return $candidate;
            }

            throw new RuntimeException(sprintf('%s file "%s" does not exist or is not readable.', $label, $candidate));
        }

        foreach ($defaultCandidates as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(sprintf(
            'No readable %s file found. Checked: %s',
            $label,
            implode(', ', $defaultCandidates),
        ));
    }

    private function toAbsolutePath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return $this->projectDir . '/' . ltrim($path, '/');
    }
}
