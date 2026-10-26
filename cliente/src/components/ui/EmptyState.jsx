import { SearchX } from 'lucide-react'

export default function EmptyState({ title, children }) {
  return (
    <div className="state">
      <SearchX size={36} aria-hidden="true" />
      <h3>{title}</h3>
      {children && <p>{children}</p>}
    </div>
  )
}
