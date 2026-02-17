import type { Customer, Order } from '../types/domain'

const API_BASE_URL = (import.meta.env.VITE_API_URL ?? '/api').replace(/\/+$/, '')

class ApiError extends Error {
  readonly status: number

  constructor(message: string, status: number) {
    super(message)
    this.name = 'ApiError'
    this.status = status
  }
}

async function requestJson<T>(path: string): Promise<T> {
    console.log(`Requesting API: ${API_BASE_URL}${path}`)
  const response = await fetch(`${API_BASE_URL}${path}`)
  if (!response.ok) {
    let message = `Erreur API (${response.status})`

    try {
      const body = (await response.json()) as { message?: string }
      if (typeof body.message === 'string' && body.message.trim() !== '') {
        message = body.message
      }
    } catch {
      // Keep the default message when response body is not JSON.
    }

    throw new ApiError(message, response.status)
  }

  return (await response.json()) as T
}

export async function fetchCustomers(): Promise<Customer[]> {
  return requestJson<Customer[]>('/customers')
}

export async function fetchCustomerOrders(customerId: number): Promise<Order[]> {
  return requestJson<Order[]>(`/customers/${customerId}/orders`)
}

export { ApiError, API_BASE_URL }
