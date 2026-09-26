<script setup>
const mobileMenuOpen = ref(false);
const dropdownOpen = ref(false);
const headerHidden = ref(false);
const mobileDropDown = ref(false);

// Traccia quale colore (Tutti/Bianchi/Rossi) sta mostrando il sotto-menu
// effervescenza, sia su desktop (hover) che su mobile (click)
const activeColorDesktop = ref(null);
const activeColorMobile = ref(null);

const canShowTabs = reactive({
  "/home": true,
  "/vini": true,
  "/eventi": true,
  "/about": true,
})

// Primo livello: solo colori (Fermi/Mossi/Frizzanti sono stati tolti da qui)
const colori = [
  { key: 'tutti', label: 'Tutti', value: null },
  { key: 'bianco', label: 'Bianchi', value: 'Bianco' },
  { key: 'rosso', label: 'Rossi', value: 'Rosso' },
];

// Secondo livello: effervescenza, mostrato di lato quando si passa sopra un colore
const effervescenze = [
  { label: 'Fermi', value: 'Fermo' },
  { label: 'Mossi', value: 'Mosso' },
  { label: 'Frizzanti', value: 'Frizzante' },
];

// Costruisce l'href finale mettendo insieme colore (c) ed effervescenza (e)
function buildHref(colore, eff) {
  const params = new URLSearchParams();
  if (colore) params.set('c', colore);
  if (eff) params.set('e', eff);
  const qs = params.toString();
  return `/vini${qs ? '?' + qs : ''}`;
}

const route = useRoute();

if (canShowTabs[route.path] != undefined) {
  canShowTabs[route.path] = false;
}

const dropDownRef = ref(null);
const viniSpanRef = ref(null);
const headerRef = ref(null);
const hamburgerRef = ref(null);

let lastScroll = 0;
let headerHeight = 0;

function handleScroll() {
  const current = window.scrollY;
  if (current > lastScroll && current > headerHeight * 2) {
    headerHidden.value = true;
    dropdownOpen.value = false;
  } else if (current < lastScroll) {
    headerHidden.value = false;
  }
  lastScroll = current;
}

const handleClickOutside = (e) => {
  if (hamburgerRef.value.contains(e.target)) {
    mobileMenuOpen.value = true;
  } else if (mobileMenuOpen.value && !headerRef.value.contains(e.target)) {
    mobileMenuOpen.value = false;
  }

  if (viniSpanRef.value?.contains(e.target)) {
    dropdownOpen.value = true;
  } else if (dropdownOpen.value && !dropDownRef.value.contains(e.target)) {
    dropdownOpen.value = false;
  }
}

// Quando il menu "Vini" si chiude, resetta anche l'eventuale sotto-menu aperto
watch(dropdownOpen, (val) => {
  if (!val) activeColorDesktop.value = null;
});

watch(mobileDropDown, (val) => {
  if (!val) activeColorMobile.value = null;
});

onMounted(() => {
  headerHeight = headerRef.value?.offsetHeight || 80
  window.addEventListener('scroll', handleScroll)
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScroll)
  document.removeEventListener('click', handleClickOutside)
})
</script>
<template>
  <header :class="{ 'header-hidden': headerHidden }" ref="headerRef">
    <div class="header-top">
      <a href="/home" class="logo">
        <img src="http://damatta.ct.ws/img/loghi/loghi1.webp" v-on:error="this.onerror=null; this.src=`http://damatta.ct.ws/img/loghi/loghi1.webp`;"/>
      </a>

      <!-- Menu Desktop -->
      <nav class="nav">
        <div class="nav-item" v-if="canShowTabs['/vini']">
          <a href="/vini" class="nav-link" ref="viniSpanRef" v-on:mouseover="dropdownOpen = true">Vini</a>
          <transition name="fade">
            <div class="desktop-categorie" v-if="dropdownOpen" ref="dropDownRef">
              <div
                class="categoria-item"
                v-for="colore in colori"
                :key="colore.key"
                @mouseover="activeColorDesktop = colore.key"
              >
                <a :href="buildHref(colore.value, null)">{{ colore.label }}</a>
                <transition name="fade">
                  <div
                    class="desktop-categorie desktop-categorie--side"
                    v-if="activeColorDesktop === colore.key"
                  >
                    <a
                      v-for="eff in effervescenze"
                      :key="eff.value"
                      :href="buildHref(colore.value, eff.value)"
                    >{{ eff.label }}</a>
                  </div>
                </transition>
              </div>
            </div>
          </transition>
        </div>
        <a class="nav-link" href="/home" v-if="canShowTabs['/home']">Home</a>
        <a class="nav-link" href="/eventi" v-if="canShowTabs['/eventi']">Eventi</a>
        <a class="nav-link" href="/about" v-if="canShowTabs['/about']">Visitaci</a>
      </nav>

      <!-- Hamburger Toggle -->
      <div class="hamburger" ref="hamburgerRef">
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" v-if="mobileMenuOpen">
      <a class="nav-link" href="/home" v-if="canShowTabs['/home']">Home</a>
      <div class="mobile-vini" v-if="canShowTabs['/vini']">
        <div class="mobile-vini-row">
          <a class="nav-link" href="/vini">I Vini</a>
          <span class="mobile-toggle" @click="mobileDropDown = !mobileDropDown">{{ mobileDropDown ? '▾' : '▸' }}</span>
        </div>
        <transition name="fade">
          <div v-if="mobileDropDown" class="mobile-categorie">
            <div class="categoria-item-mobile" v-for="colore in colori" :key="colore.key">
              <div class="mobile-vini-row">
                <a :href="buildHref(colore.value, null)">{{ colore.label }}</a>
                <span
                  class="mobile-toggle"
                  @click="activeColorMobile = activeColorMobile === colore.key ? null : colore.key"
                >{{ activeColorMobile === colore.key ? '▾' : '▸' }}</span>
              </div>
              <transition name="fade">
                <div v-if="activeColorMobile === colore.key" class="mobile-categorie">
                  <a
                    v-for="eff in effervescenze"
                    :key="eff.value"
                    :href="buildHref(colore.value, eff.value)"
                  >{{ eff.label }}</a>
                </div>
              </transition>
            </div>
          </div>
        </transition>
      </div>
      <a class="nav-link" href="/eventi" v-if="canShowTabs['/eventi']">Eventi</a>
      <a class="nav-link" href="/about" v-if="canShowTabs['/about']">Visitaci</a>
    </div>
  </header>
</template>

<style scoped>
header {
  position: fixed;
  top: 0;
  width: 100vw;
  background: #f1dab1;
  z-index: 10;
  transition: transform 0.3s ease;
  border-bottom: 1px solid rgba(47, 72, 88, 0.4);
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.8rem 2rem;
}

.logo {
  max-width: 20%;
  text-decoration: none;
}

.logo img {
  max-height: 60px;
}

.nav {
  display: flex;
  gap: 2rem;
  align-items: center;
}

.nav-item {
  position: relative;
}

.nav-link {
  text-decoration: none;
  color: #333;
  font-weight: 500;
  cursor: pointer;
  transition: color 0.3s;
}

.nav-link:hover {
  color: #a00;
}

.dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  display: none;
  flex-direction: column;
  background: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  z-index: 10;
}

.nav-item:hover .dropdown {
  display: flex;
}

.dropdown div {
  padding: 0.4rem 1rem;
  white-space: nowrap;
  cursor: pointer;
  color: #333;
}

.dropdown div:hover {
  background: #f4f4f4;
}

/* Hamburger */
.hamburger {
  display: none;
  flex-direction: column;
  gap: 4px;
  cursor: pointer;
}

.hamburger div {
  width: 25px;
  height: 3px;
  background: #333;
}

/* Mobile menu */
.mobile-menu {
  display: none;
  flex-direction: column;
  background: #fff;
  padding: 1rem 2rem;
  width: 100%;
  font-size: 18px;
  row-gap: 2px;
  border-top: 1px solid #eee;
}

.mobile-menu a {
  margin: 3px 0;
  text-decoration: none;
  color: #333;
  font-weight: 500;
}

.mobile-vini span {
  cursor: pointer;
  display: inline-block;
}

.mobile-vini .dropdown div {
  padding-left: 1rem;
}

/* Scroll hide */
.header-hidden {
  transform: translateY(-100%);
}

/* Responsive */
@media (max-width: 768px) {
  .nav {
    display: none;
  }

  .hamburger {
    display: flex;
  }

  .mobile-menu {
    display: flex;
  }
}

.mobile-categorie {
  display: flex;
  flex-direction: column;
  margin-bottom: 0.5rem;
  padding: 0.5rem;
  border-left: 2px solid #aaa;
  margin-left: 0.5rem;
  gap: 0.4rem;
  transition: all 0.3s ease;
}

.mobile-categorie a {
  padding: 0.5rem;
  background: #f8f8f8;
  border-radius: 6px;
  font-size: 0.95rem;
  color: #333;
  cursor: pointer;
  text-decoration: none;
}

.mobile-categorie a:hover {
  background: #eee;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.desktop-categorie {
  position: absolute;
  top: 120%;
  left: 0;
  display: flex;
  flex-direction: column;
  background: #fff;
  padding: 1rem;
  gap: 0.4rem;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  border: 1px solid #eee;
  min-width: 200px;
  z-index: 99;
}

.desktop-categorie a {
  padding: 0.5rem;
  background: #f8f8f8;
  border-radius: 6px;
  color: #333;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.2s ease;
}

.desktop-categorie a:hover {
  background: #eee;
}

@media (max-width: 768px) {
  .desktop-categorie {
    display: none;
  }
}

/* --- Aggiunte per il sotto-menu Mossi/Fermi/Frizzanti --- */

.categoria-item {
  position: relative;
}

.categoria-item > a {
  display: block;
}

/* Il sotto-menu riusa esattamente le stesse regole di .desktop-categorie,
   qui sovrascriviamo solo la posizione per farlo comparire a lato */
.desktop-categorie--side {
  top: -1rem;
  left: auto;
  right: 105%;
  margin-right: 0.5rem;
}

/* Riga con etichetta cliccabile (link) + freccia di apertura sotto-menu (mobile) */
.mobile-vini-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.mobile-vini-row a {
  flex: 1;
}

.mobile-toggle {
  cursor: pointer;
  padding: 0 0.5rem;
  color: #333;
  user-select: none;
}

.categoria-item-mobile {
  display: flex;
  flex-direction: column;
}
</style>