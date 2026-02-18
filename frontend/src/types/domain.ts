export type Customer = {
  id: number
  title: number | null
  lastname: string | null
  firstname: string | null
  postal_code: number | null
  city: string | null
  email: string | null
}

export type Order = {
  lastname: string | null
  firstname: string | null
  purchase: string
  product_quantity: number
  price: number
  purchase_identifier: string
  currency: string
  date: string
}
