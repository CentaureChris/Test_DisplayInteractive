import { MemoryRouter } from 'react-router-dom'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { render, screen, waitFor } from '@testing-library/react'
import CustomerComponent from './CustomerComponent'
import { fetchCustomers } from '../api/customers'
import type { Customer } from '../types/domain'

vi.mock('../api/customers', () => ({
  fetchCustomers: vi.fn(),
}))

function renderInTable() {
  return render(
    <MemoryRouter>
      <table>
        <tbody>
          <CustomerComponent />
        </tbody>
      </table>
    </MemoryRouter>,
  )
}

describe('CustomerComponent', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('affiche loading puis les clients quand fetchCustomers réussit', async () => {
    const customers: Customer[] = [
      {
        id: 1,
        title: 1,
        lastname: 'Dupont',
        firstname: 'Alice',
        postal_code: 75001,
        city: 'Paris',
        email: 'alice.dupont@example.com',
      },
      {
        id: 2,
        title: 2,
        lastname: 'Martin',
        firstname: 'Bob',
        postal_code: 69001,
        city: 'Lyon',
        email: 'bob.martin@example.com',
      },
    ]

    vi.mocked(fetchCustomers).mockResolvedValueOnce(customers)

    renderInTable()

    expect(screen.getByText('Chargement des clients...')).toBeInTheDocument()

    expect(await screen.findByText('Dupont')).toBeInTheDocument()
    expect(screen.getByText('Martin')).toBeInTheDocument()
    expect(screen.getByText('Madame')).toBeInTheDocument()
    expect(screen.getByText('Monsieur')).toBeInTheDocument()

    await waitFor(() => {
      expect(screen.queryByText('Chargement des clients...')).not.toBeInTheDocument()
    })

    expect(fetchCustomers).toHaveBeenCalledTimes(1)
  })
})