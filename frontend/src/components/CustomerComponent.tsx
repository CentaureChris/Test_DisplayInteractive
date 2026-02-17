import {useEffect, useState} from 'react'
import { fetchCustomers } from '../api/customers'
import type { Customer } from '../types/domain'  
import ShowButton from './ShowButton'

export default function CustomerComponent() {
  const [customers, setCustomers] = useState<Customer[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [errorMessage, setErrorMessage] = useState<string | null>(null)

  useEffect(() => {
    fetchCustomers()
      .then(data => {
        setCustomers(data)
        setErrorMessage(null)
      })
      .catch(error => {
        console.error('Erreur lors de la récupération des clients:', error)
        setErrorMessage('Impossible de charger les clients.')
      })
      .finally(() => setIsLoading(false))
  }, [])

  function listAllCustomers() {
    if (isLoading) {
      return (
        <tr className="state-row">
          <td colSpan={8}>Chargement des clients...</td>
        </tr>
      )
    }

    if (errorMessage !== null) {
      return (
        <tr className="state-row state-error">
          <td colSpan={8}>{errorMessage}</td>
        </tr>
      )
    }

    if (customers.length === 0) {
      return (
        <tr className="state-row">
          <td colSpan={8}>Aucun client trouvé.</td>
        </tr>
      )
    }

    return customers.map((customer: Customer) => (
      <tr key={customer.id}>
        <td>{customer.id}</td>
        <td>{customer.title === 1 ? "Madame" : customer.title === 2 ? "Monsieur" : "-"}</td>
        <td>{customer.lastname}</td>
        <td>{customer.firstname}</td>
        <td>{customer.postal_code}</td>
        <td>{customer.city}</td>
        <td>{customer.email}</td>
        <td><ShowButton customerId={customer.id} /></td>
      </tr>
    ))
  }
  return (
    <>
      {listAllCustomers()}
    </>
  )
}
