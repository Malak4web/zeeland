import { createApp } from 'vue'
import App from './App.vue'
import { reveal } from './directives/reveal.js'
import { initBlog } from './blog.js'
import { initTheme } from '../theme.js'
import '../../css/site.css'

// The theme is already painted by the boot snippet in <head>; this only wires
// the switch, so it runs for the landing page and the blog alike.
initTheme()

// The landing page mounts Vue; the blog pages are server-rendered and share
// only the stylesheet, so the mount is conditional rather than assumed.
const root = document.querySelector('#app')
if (root) {
  createApp(App).directive('reveal', reveal).mount(root)
} else {
  initBlog()
}
