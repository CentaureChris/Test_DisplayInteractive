import { useEffect, useState } from "react";
import { fetchCustomerOrders } from "../api/customers";
import type { Order } from "../types/domain";

export type DisplayCurrency = "EUR" | "USD";

export type OrdersTotal = {
  value: number;
  quantity: number;
};

type OrderComponentProps = {
  customerId: number;
  displayCurrency: DisplayCurrency;
  onTotalChange: (total: OrdersTotal) => void;
  onCustomerNameChange: (name: string) => void;
};

const EUR_TO_USD = 1.1826;
const USD_TO_EUR = 1 / EUR_TO_USD;

function normalizeCurrency(currency: string): DisplayCurrency | null {
  const normalized = currency.trim().toUpperCase();
  if (normalized === "EUR" || normalized === "USD") {
    return normalized;
  }

  return null;
}

function convertAmount(
  amount: number,
  from: DisplayCurrency,
  to: DisplayCurrency,
): number {
  if (from === to) {
    return amount;
  }

  return from === "EUR" ? amount * EUR_TO_USD : amount * USD_TO_EUR;
}

export default function OrderComponent({
  customerId,
  displayCurrency,
  onTotalChange,
  onCustomerNameChange,
}: OrderComponentProps) {
  const [orders, setOrders] = useState<Order[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const hasInvalidCustomerId = Number.isNaN(customerId) || customerId <= 0;

  useEffect(() => {
    if (hasInvalidCustomerId) {
      onTotalChange({ value: 0, quantity: 0 });
      return;
    }

    let isCancelled = false;

    fetchCustomerOrders(customerId)
      .then(data => {
        if (isCancelled) {
          return;
        }
        setOrders(data);
        onCustomerNameChange(` ${data[0]?.firstname ?? ""} ${data[0]?.lastname ?? ""}`);
      })
      .catch(error => {
        if (isCancelled) {
          return;
        }

        setOrders([]);
        console.error("Erreur lors de la récupération des commandes:", error);
        setErrorMessage("Impossible de charger les commandes.");
      })
      .finally(() => {
        if (isCancelled) {
          return;
        }

        setIsLoading(false);
      });

    return () => {
      isCancelled = true;
    };
  }, [customerId, hasInvalidCustomerId, onTotalChange, onCustomerNameChange]);

  useEffect(() => {
    if (hasInvalidCustomerId || errorMessage !== null || isLoading) {
      onTotalChange({ value: 0, quantity: 0 });
      return;
    }

    const totalValue = orders.reduce((sum, order) => {
      const sourceCurrency =
        normalizeCurrency(order.currency) ?? displayCurrency;
      const convertedPrice = convertAmount(
        order.price,
        sourceCurrency,
        displayCurrency,
      );

      return sum + convertedPrice * order.product_quantity;
    }, 0);

    const totalQuantity = orders.reduce(
      (sum, order) => sum + order.product_quantity,
      0,
    );
    onTotalChange({ value: totalValue, quantity: totalQuantity });
  }, [
    displayCurrency,
    errorMessage,
    hasInvalidCustomerId,
    isLoading,
    onTotalChange,
    orders,
  ]);

  if (hasInvalidCustomerId) {
    return (
      <tr className="state-row state-error">
        <td colSpan={7}>Client invalide.</td>
      </tr>
    );
  }

  if (isLoading) {
    return (
      <tr className="state-row">
        <td colSpan={7}>Chargement des commandes...</td>
      </tr>
    );
  }

  if (errorMessage !== null) {
    return (
      <tr className="state-row state-error">
        <td colSpan={7}>{errorMessage}</td>
      </tr>
    );
  }

  if (orders.length === 0) {
    return (
      <tr className="state-row">
        <td colSpan={7}>Aucune commande pour ce client.</td>
      </tr>
    );
  }

  return (
    <>
      {orders.map(order => {
        const sourceCurrency =
          normalizeCurrency(order.currency) ?? displayCurrency;
        const convertedPrice = convertAmount(
          order.price,
          sourceCurrency,
          displayCurrency,
        );

        return (
          <tr key={`${order.purchase_identifier}-${order.date}`}>
            <td>{new Date(order.date).toLocaleDateString()}</td>
            <td>{order.purchase_identifier}</td>
            {/* <td>{order.lastname} {order.firstname}</td> */}
            <td>{order.purchase}</td>
            <td>{order.product_quantity}</td>
            <td>{convertedPrice.toFixed(2)}</td>
            <td>{displayCurrency}</td>
          </tr>
        );
      })}
    </>
  );
}
