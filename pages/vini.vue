<script setup>
import { getData, toSelectOptions } from '~/assets/script';
useHead({ title: "Vini" });

const errorObj = reactive({
  title: "",
  msg: "",
  link: "",
  linkMsg: ""
});
const error = ref(false);

const route = useRoute();

const page = ref(1);
const limit = 15;

const filtri = reactive({
  search: "",
  Marca: "",
  colore: "",
  effervescenza: "",
  sortBy: "",
  order: "ASC",
  limit: 15,
  offset: (page.value - 1) * limit,
  firstTime: 1
});

if (route.query.c) {
  filtri.colore = route.query.c;
  filtri.getAll = true;
} else if (route.query.e) {
  filtri.effervescenza = route.query.e;
  filtri.getAll = true;
}

const inEvidenzia = ref(null), vini = ref(null);

let oldFiltri = {
  search: "test",
  Marca: "",
  colore: "",
  effervescenza: "",
  sortBy: "",
  order: "ASC",
};

let listaVini = null;


function load(loadMore = false) {
  if (filtri.search != oldFiltri.search || filtri.Marca != oldFiltri.Marca ||
    filtri.colore != oldFiltri.colore || filtri.effervescenza != oldFiltri.effervescenza ||
    filtri.sortBy != oldFiltri.sortBy || filtri.order != oldFiltri.order
  ) {
    oldFiltri.search = filtri.search;
    oldFiltri.Marca = filtri.Marca;
    oldFiltri.colore = filtri.colore;
    oldFiltri.effervescenza = filtri.effervescenza;
    oldFiltri.sortBy = filtri.sortBy;
    oldFiltri.order = filtri.order;

    getData("/getVini", filtri).then(function (data) {
      if (filtri.getAll) {
        delete filtri.getAll;
      }

      if (!data.ok) {
        error.value = true;
        errorObj.msg = data.msg;
      } else if (!filtri.firstTime) {

        if (!loadMore) {
          vini.value = data.vini;
          scrollToVini();
        } else {
          vini.value.push(...data.vini);
          page.value++;
        }
      } else {
        filtri.firstTime = 0;
        inEvidenzia.value = data.inEvidenzia;
        vini.value = data.vini;

        if (route.query.c || route.query.e) {
          scrollToVini();
        }
      }
    });
  } else {
    scrollToVini();
  }
}

function scrollToVini() {
  if (listaVini == null) {
    listaVini = document?.querySelector('.vini-lista');
  }

  requestAnimationFrame(() => {
    listaVini?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}


const mostraFiltri = ref(false);

function toggleFiltri() {
  mostraFiltri.value = !mostraFiltri.value;
}

function enterAnim(el) {
  el.style.height = '0'
  el.style.opacity = '0'
  requestAnimationFrame(() => {
    el.style.transition = 'height 0.3s ease, opacity 0.3s ease'
    el.style.height = el.scrollHeight + 14 + 'px'
    el.style.opacity = '1'
  })
}

function leaveAnim(el) {
  el.style.height = el.scrollHeight + 'px'
  el.style.opacity = '1'
  requestAnimationFrame(() => {
    el.style.transition = 'height 0.3s ease, opacity 0.3s ease'
    el.style.height = '0'
    el.style.opacity = '0'
  })
}

load();
</script>

<template>
  <MyLoading :show="!error && !vini" />
  <myHeader v-if="!error" />
  <main v-if="!error">
    <section class="filtri-container">
      <div class="search-bar">
        <div>
          <MyInput type="text" v-model="filtri.search" placeholder="Cerca un vino..." id="cercaInput"
            @invioPremuto="load()" />
          <button id="cerca" @click="load()">Cerca</button>
        </div>
        <button class="filtri-toggle" @click="toggleFiltri">
          {{ mostraFiltri ? '▲' : '▼' }}
        </button>
      </div>

      <transition @enter="enterAnim" @leave="leaveAnim">
        <div v-show="mostraFiltri" class="filtri-avanzati">
          <div class="riga-filtri">
            <MyInput label="Colore" :defaultValue="route.query.c ?? 'Tutti'" v-model="filtri.colore" type="select"
              :data="toSelectOptions([
                'Tutti', 'Rosso', 'Bianco', 'Rosato', 'Arancione', 'Grigio'
              ])" />

            <MyInput label="Effervescenza" :defaultValue="route.query.e ?? 'Tutti'" v-model="filtri.effervescenza"
              type="select" :data="toSelectOptions([
                'Tutti', 'Fermo', 'Mosso', 'Frizzante', 'Spumante'
              ])" />
            <MyInput label="Marca" placeholder="Cerca per marca" v-model="filtri.Marca" />
          </div>

          <div class="riga-ordina">
            <MyInput label="Ordina per" v-model="filtri.sortBy" type="select" :data="toSelectOptions([
              'Marca', 'Nome', 'Anno', 'Costo', 'Effervescenza', 'Colore', 'Gradazione', 'Quantita'
            ])" />
            <MyInput label="Senso" v-model="filtri.order" type="select" :data="[
              { text: 'Crescente', value: 'ASC' },
              { text: 'Decrescente', value: 'DESC' }
            ]" />
          </div>
        </div>
      </transition>
    </section>

    <section class="vini-evidenziati">
      <h2>Vini in evidenza</h2>
      <div class="flex-container">
        <a v-for="vino in inEvidenzia" :key="vino.Id" class="card evidenziato" :href="`/vino?id=${vino.Id}`">
          <div class="etichetta">In Evidenza</div>
          <img :src="`/img/vini/${vino.Id}.webp`" :alt="vino.Nome" />
          <div class="info">
            <h3>{{ vino.Nome + ' ' + (vino?.Anno ? `(${vino.Anno})` : '') }}</h3>
            <p>{{ vino.Marca }}</p>
            <strong v-if="vino.Offerta">
              <span class="prezzo-originale">{{ vino.Costo }} €</span>
              <span class="prezzo-offerta">{{ vino.Offerta }} €</span>
            </strong>
            <strong v-else>{{ vino.Costo }} €</strong>
          </div>
          <div class="quantita-bottiglia">{{ vino.Quantita }} L</div>
          <div class="colore-bottiglia" :class="vino.Colore">{{ vino.Colore }}</div>
        </a>
      </div>
    </section>

    <hr>

    <section class="vini-lista" id="wines">
      <h2>Risultati</h2>
      <div class="flex-container" v-if="vini?.length > 0">
        <a v-for="vino in vini" :key="vino.Id" class="card" :class="{ 'evidenziato': vino.Evidenzia }"
          @click="vaiDettaglio(vino.Id)" :href="`/vino?id=${vino.Id}`">
          <div v-if="vino.Evidenzia" class="etichetta">In Evidenza</div>
          <img :src="`/img/vini/${vino.Id}.webp`" :alt="vino.Nome" />
          <div class="info">
            <h3>{{ vino.Nome + ' ' + (vino?.Anno ? `(${vino.Anno})` : '') }}</h3>
            <p>{{ vino.Marca }}</p>
            <strong v-if="vino.Offerta">
              <span class="prezzo-originale">{{ vino.Costo }} €</span>
              <span class="prezzo-offerta">{{ vino.Offerta }} €</span>
            </strong>
            <strong v-else>{{ vino.Costo }} €</strong>
          </div>
          <div class="quantita-bottiglia">{{ vino.Quantita }} L</div>
          <div class="colore-bottiglia" :class="vino.Colore">{{ vino.Colore }}</div>
        </a>
      </div>
      <h1 v-else style="color: rgba(84, 84, 84, 1);">Nessun risultato {{ filtri.search != "" ? `per ' ${filtri.search}
        '` :
        filtri.Marca != "" ? `per ' ${filtri.Marca} '` : ""}}</h1>
    </section>
  </main>
  <myFooter v-if="!error" />
  <myError v-else :obj="errorObj" />
</template>

<style scoped>
.colore-bottiglia.Rosso {
  background-color: #7B0F15;
  color: #eeeeee;
}

.colore-bottiglia.Bianco {
  background-color: #F5E26B;
  color: #333;
}

.colore-bottiglia.Rosato {
  background-color: #EFA6A6;
  color: #333;
}

.colore-bottiglia.Arancione {
  background-color: #D9822B;
  color: #eeeeee;
}

.colore-bottiglia.Grigio {
  background-color: #D8C3A5;
  color: #333;
}

main {
  display: flex;
  flex-direction: column;
  width: 100%;
  padding: 2vh 6vw;
  padding-bottom: 6vh;
  margin-top: 90px;
  row-gap: 4vh;
  background: url("/img/lightBackground.png") repeat;
}

h1 {
  font-size: 3vw;
  margin-top: 4vh;
}

hr {
  display: none;
}

.filtri-container {
  display: flex;
  flex-direction: column;
  width: 100%;
  row-gap: 10px;
}

.search-bar {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  background: #f9f9f9;
  padding: 12px 16px;
  border-radius: 8px;
  border-left: 5px solid #800000;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.search-bar>div {
  display: flex;
  width: 100%;
  gap: 10px;
}

.search-bar input {
  flex: 1;
}

#cerca {
  background-color: #800000;
  color: white;
  padding: 10px 16px;
  border: none;
  cursor: pointer;
  font-size: 15px;
  border-radius: 8px;
  transition: background 0.3s;
  min-width: 15%;
}

#cerca:hover {
  background-color: #a00000;
}

.filtri-toggle {
  background: none;
  border: none;
  font-size: 20px;
  width: 100%;
  cursor: pointer;
  color: #800000;
  padding: 2px 10px;
  line-height: 1;
}

.filtri-avanzati {
  background: #f9f9f9;
  padding: 12px 16px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  animation: fadeIn 0.3s ease-in-out;
  border-left: 5px solid #800000;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.riga-filtri {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.riga-ordina {
  display: flex;
  gap: 12px;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.25s ease;
  overflow: hidden;
  max-height: 500px;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  max-height: 0;
}

.filtri-wrapper button:hover {
  background-color: #a00000;
}

.quantita-bottiglia,
.colore-bottiglia {
  position: absolute;
  bottom: 10px;
  font-weight: bold;
  left: 10px;
  background-color: #eeeeee;
  color: #333;
  padding: 4px 8px;
  font-size: 13px;
  border-radius: 4px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

.colore-bottiglia {
  top: 10px;
  bottom: unset;
}


.flex-container {
  display: flex;
  flex-wrap: wrap;
  column-gap: 5vw;
  row-gap: 15vh;
  justify-content: center;
  width: 100%;
}

.vini-evidenziati .flex-container {
  row-gap: 6vh;
}

.card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s ease;
  position: relative;
  display: flex;
  flex-direction: column;
  width: calc(33.333% - 16px);
  max-width: 500px;
  /* min-width: 335px; */
  color: initial;
  text-decoration: none;
  height: 480px;
  row-gap: 8px;
  padding: 16px 0;
  flex: 1 1 calc(33% - 5vw);
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.card img {
  max-width: 100%;
  min-height: 200px;
  flex: 1;
  object-fit: scale-down;
}

.card .info {
  text-align: center;
}

.card .info h3 {
  margin: 5px 0;
  font-size: 17px;
}

h2 {
  margin: 5vh 0;
}

.card .info p {
  color: #666;
  margin: 4px 0;
}

.card .info strong {
  display: block;
  margin-top: 6px;
  font-size: 17px;
  color: #222;
}

.etichetta {
  position: absolute;
  top: 8px;
  right: 8px;
  background-color: #d90000;
  color: white;
  padding: 5px 10px;
  font-size: 12px;
  border-radius: 6px;
  text-transform: uppercase;
  font-weight: bold;
}

.vini-evidenziati,
.vini-lista {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.prezzo-originale {
  text-decoration: line-through;
  color: #888;
  margin-right: 8px;
  font-size: 15px;
}

.prezzo-offerta {
  color: #c40000;
  font-weight: bold;
  font-size: 17px;
}


@media screen and (max-width: 1024px) {
  main {
    padding: 2vh 2vw;
    padding-bottom: 6vh;
  }

  .vini-evidenziati .flex-container {
    justify-content: space-evenly;
  }

  .card {
    flex: 1 1 calc(50% - 5vw);
    max-width: calc(50% - 5vw);
  }
}

@media (max-width: 650px) {
  .flex-container .card {
    flex: 1 1 100%;
    max-width: 100%;
  }

  .flex-container {
    row-gap: 10vh;
  }
}

@media (max-width: 768px) {
  main {
    padding: 0;
    align-items: center;
  }

  hr {
    display: block;
    width: 85%;
    margin: 0 40vw;
  }

  section:not(:first-child) {
    padding: 2vh 4vw;
    margin: 0;
  }

  .filtri-wrapper {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    width: 100%;
    padding: 16px 12px;
  }

  .filtri-wrapper input,
  .filtri-wrapper select {
    font-size: 14px;
    padding: 10px 8px;
  }

  #cerca {
    min-width: 25%;
  }

  .flex-container {
    justify-content: center;
  }

  .card img {
    height: 180px;
  }

  .filtri-container {
    padding: 2vh 2vw;
  }
}

@media screen and (max-width:480px) {

  .filtri-wrapper input,
  .filtri-wrapper select,
  #cerca {
    font-size: 12px;
  }

  h2 {
    font-size: 26px;
  }

  .filtri-wrapper>div:last-child {
    column-gap: 5px;
  }

  .filtri-wrapper {
    padding: 16px 10px;
  }

  .card {
    max-width: unset;
    width: 100%;
  }
}

@media screen and (max-width: 360px) {
  .riga-ordina {
    align-self: flex-start;
    justify-content: space-between;
    gap: 0;
    width: 100%;
  }

  .riga-ordina>.input-group {
    width: 120px;
  }

  .filtri-container>div {
    padding: 8px;
  }


  .card {
    min-width: 100%;
  }
}
</style>