import { useCallback, useState } from "react";
import OrderComponent from "../components/OrderComponent";
import BackButton from "../components/BackButton";
import { useParams } from "react-router-dom";
import type {
  DisplayCurrency,
  OrdersTotal,
} from "../components/OrderComponent";

export default function OrdersPage() {
  const { id } = useParams();
  const customerId = Number(id);
  const [displayCurrency, setDisplayCurrency] =
    useState<DisplayCurrency>("EUR");
  const [total, setTotal] = useState<OrdersTotal>({ value: 0, quantity: 0 });
  const [customerName, setCustomerName] = useState<string>("");

  const toggleCurrency = useCallback(() => {
    setDisplayCurrency(current => (current === "EUR" ? "USD" : "EUR"));
  }, []);

  const handleTotalChange = useCallback((nextTotal: OrdersTotal) => {
    setTotal(nextTotal);
  }, []);

  const handleCustomerNameChange = useCallback((name: string) => {
    setCustomerName(name);
  }, []);

  return (
    <section className="page-shell">
      <header className="page-header">
        <div>
          <h1 className="page-title">Liste des commandes</h1>
          <p className="page-subtitle">
            Client: {customerName}
          </p>
        </div>
        <div className="page-actions">
          <button
            type="button"
            className="action-btn action-btn-secondary"
            onClick={toggleCurrency}
          >
            Basculer en {displayCurrency === "EUR" ? "USD" : "EUR"}
          </button>
          <BackButton />
        </div>
      </header>
      <div className="table-wrap">
        <table className="data-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Identifiant commande</th>
              {/* <th>Nom</th> */}
              <th>Produit</th>
              <th>Quantité</th>
              <th>Prix</th>
              <th>Devise</th>
            </tr>
          </thead>
          <tbody>
            <OrderComponent
              key={customerId}
              customerId={customerId}
              displayCurrency={displayCurrency}
              onTotalChange={handleTotalChange}
              onCustomerNameChange={handleCustomerNameChange}
            />
            <tr className="totalCol">
              <td colSpan={3}>Total</td>
              <td>{total.quantity}</td>
              <td>{total.value.toFixed(2)}</td>
              <td>{displayCurrency}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  );
}
