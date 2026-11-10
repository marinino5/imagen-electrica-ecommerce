import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
// Los estilos globales van primero: así los de cada componente pueden sobrescribirlos
import './styles/variables.css'
import './styles/base.css'
import './styles/system.css'
import './styles/rd.css'
import './styles/ie.css'
import './styles/bgfx.css'
import './styles/fase1.css'
import App from './App'

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <BrowserRouter>
      <App />
    </BrowserRouter>
  </StrictMode>,
)
