import { useNavigate } from 'react-router-dom'

export default function BackButton() {
  const navigate = useNavigate()

  return (
    <button
      type="button"
      className="action-btn action-btn-secondary"
      onClick={() => navigate('/customers')}
    >
      Retour clients
    </button>
  )
}
