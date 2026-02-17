import { useState, useEffect } from "react";
import { fetchCustomerOrders } from "../api/customers";
import type { Order } from "../types/domain";

export default function OrderComponent({ customerId }: { customerId: number }) {
  const [orders, setOrders] = useState<Order[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  useEffect(() => {
    if (Number.isNaN(customerId)) {
      setOrders([]);
      setErrorMessage("Client invalide.");
      setIsLoading(false);
      return;
    }

    setIsLoading(true);
    fetchCustomerOrders(customerId)
      .then((data) => {
        setOrders(data);
        setErrorMessage(null);
      })
      .catch(error => {
        console.error("Erreur lors de la récupération des commandes:", error);
        setErrorMessage("Impossible de charger les commandes.");
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [customerId]);

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
      {orders.map(order => (
        <tr key={`${order.purchase_identifier}-${order.date}`}>
          <td>{order.lastname}</td>
          <td>{order.purchase}</td>
          <td>{order.product_quantity}</td>
          <td>{order.price}</td>
          <td>{order.purchase_identifier}</td>
          <td>{order.currency}</td>
          <td>{new Date(order.date).toLocaleDateString()}</td>
        </tr>
      ))}
    </>
  );
}
