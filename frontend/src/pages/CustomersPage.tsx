import CustomerComponent from "../components/CustomerComponent"

export default function CustomersPage() {

  return (
    <section className="page-shell">
      <header className="page-header">
        <div>
          <h1 className="page-title">Customers</h1>
        </div>

      </header>
      <div className="table-wrap">
        <table className="data-table">
            <thead>
              <tr>
                <th>id </th>
                <th>Civilité</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Code postal</th>
                <th>Ville</th>
                <th>Email</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <CustomerComponent />
            </tbody>
        </table>
      </div>
    </section>
  )
}
