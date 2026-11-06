// Etiqueta sobre los títulos: píldora pequeña en mayúsculas con un punto naranja.
export default function Eyebrow({ children, className = '', ...props }) {
  return <span className={`eyebrow ${className}`} {...props}>{children}</span>
}
