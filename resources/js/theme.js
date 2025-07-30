const html = document.documentElement
const saved = localStorage.getItem('theme')

if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  html.classList.add('dark')
} else {
  html.classList.remove('dark')
}

// Exporta função para alternar
export function toggleTheme() {
  html.classList.toggle('dark')
  const active = html.classList.contains('dark') ? 'dark' : 'light'
  localStorage.setItem('theme', active)
}
