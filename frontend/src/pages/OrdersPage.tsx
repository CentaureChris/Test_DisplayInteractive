import OrderComponent from "../components/OrderComponent"
import BackButton from "../components/BackButton"
import { useParams } from "react-router-dom"

export default function OrdersPage() {
  const { id } = useParams()
  const customerId = Number(id)
  
  return (
     <section className="page-shell">
        <header className="page-header">
          <div>
            <h1 className="page-title">Orders</h1>
            <p className="page-subtitle">Commandes du client #{Number.isNaN(customerId) ? "-" : customerId}</p>
          </div>
          <BackButton />
        </header>
        <div className="table-wrap">
          <table className="data-table">
             <thead>
              <tr>
                <th>Nom</th>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix</th>
                <th>Identifiant commande</th>
                <th>Devise</th>
                <th>Date</th>
              </tr>
             </thead>
             <tbody>
               <OrderComponent key={customerId} customerId={customerId} />
             </tbody>
          </table>
        </div>
     </section>
   )
}
