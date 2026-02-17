import { useNavigate } from 'react-router-dom';

export default function ShowButton({ customerId }: { customerId: number }) {
  const navigate = useNavigate();

  return (
    <button
      type="button"
      className="action-btn action-btn-primary"
      onClick={() => navigate(`/customers/${customerId}/orders`)}
    >
      Voir commandes
    </button>
  );
}
