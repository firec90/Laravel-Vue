import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js'

// import bootstrap
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap/dist/js/bootstrap.bundle.min.js";
//import "bootstrap" // js optional, tapi kita import aja untuk modal/button dll

createApp(App).use(router).mount('#app')

/*
const app = createApp(App)
app.use(router)
app.mount('#app')
*/