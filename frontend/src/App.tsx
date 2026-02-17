import './App.css'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import CustomersPage from './pages/CustomersPage'
import OrdersPage from './pages/OrdersPage' 


function App() {

  return (
    <BrowserRouter>
      <div className="app-frame">
        <Routes>
          <Route path="/" element={<Navigate to="/customers" replace />} />
          <Route path="/customers" element={<CustomersPage />} />
          <Route path="/customers/:id/orders" element={<OrdersPage />} />
          <Route path="*" element={<Navigate to="/customers" replace />} />
        </Routes>
      </div>
    </BrowserRouter>
  )
}

export default App
