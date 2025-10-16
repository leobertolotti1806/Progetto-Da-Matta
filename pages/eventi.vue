<script setup>
import { getData, longStringDate, getIdKeyAsArrayKey } from '~/assets/script';
useHead({ title: "Eventi" });

const eventi = ref(null);
const hideDiv = ref(false);

const page = ref(1);
const perPage = 5;

const props = defineProps({
  images: {
    Type: Array, default: [
      "/img/events/events0.webp"
    ]
  },
  editMode: false
});
const paragraphs = ref([]);
const errorObj = reactive({
  title: "",
  msg: "",
  link: "",
  linkMsg: ""
});

const error = ref(false);
defineEmits(["changeImage", "changeParagraph"]);
getData("/getParagraphs?page=eventi").then(function (esito) {
  if (!esito.ok) {
    error.value = true;
    errorObj.msg = esito?.msg;
  } else {
    paragraphs.value = getIdKeyAsArrayKey(esito.paragrafi);
  }
});

getData("/getEventi?basic").then(function (data) {
  if (!data.ok) {
    error.value = true;
    errorObj.msg = data.msg;
  } else {
    eventi.value = data.eventi;
  }
});

async function loadMore() {
  let esito = await getData(`/getEventi?limit=${perPage}&offset=${(page.value - 1) * perPage}`)

  if (!esito.ok) {
    error.value = true;
    errorObj.msg = esito.msg;
  } else {
    if (esito.eventi.length > 0) {
      hideDiv.value = false;
      eventi.value.past.push(...esito.eventi);
      page.value++;
    } else {
      hideDiv.value = true;
    }
  }
}
</script>

<template>
  <myHeader v-if="!error && !props.editMode" />
  <main v-if="!error" :class="props.editMode ? 'edit' : null">
    <section class="hero-section">
      <img :class="props.editMode ? 'edit' : null" :src="props.images[0]" alt="Immagine"
        @dblclick="props.editMode && $emit('changeImage', 0)">
      <div class="hero-overlay">
        <div class="hero-text">
          <h1 class="titolo" :class="props.editMode ? 'changeText' : null"
            @click="props.editMode && $emit('changeParagraph', 30)" v-html="paragraphs[30]"></h1>
          <h2 class="sottotitolo" :class="props.editMode ? 'changeText' : null"
            @click="props.editMode && $emit('changeParagraph', 31)" v-html="paragraphs[31]"></h2>
          <span class="paragraph" style="margin-top: 24px;" :class="props.editMode ? 'changeText' : null"
            @click="props.editMode && $emit('changeParagraph', 32)" v-html="paragraphs[32]">
          </span>
        </div>
      </div>
    </section>
    <hr>
    <h2 v-if="eventi?.next">{{ eventi?.next.length == 0 ? 'Non ci sono ancora eventi futuri in programma!' : "Prossimi eventi"}}</h2>
    <div class="eventi-container" v-if="eventi?.next && eventi?.next.length != 0">
      <a class="evento-card" v-for="evento in eventi.next" :key="evento.Id" :href="`/event?id=${evento.Id}`">
        <img :src="`/img/event/${evento.Id}.webp`" :alt="evento.Titolo" class="evento-img" />
        <div class="evento-content">
          <div>
            <h2>{{ evento.Titolo }}</h2>
            <p class="data">{{ longStringDate(evento.Data) }}</p>
          </div>
          <p class="descrizione">
            {{ evento.Descrizione.length == 180 ? evento.Descrizione + '...' : evento.Descrizione }}
          </p>
          <button class="btn-dettaglio">Vedi di più</button>
        </div>
      </a>
      <hr>
    </div>
    <div class="eventi-container" id="pastEvent">
      <h1>Eventi passati</h1>
      <div>
        <a class="evento-card" v-for="evento in eventi?.past" :key="evento.Id" :href="`/event?id=${evento.Id}`">
          <img :src="`/img/event/${evento.Id}.webp`" :alt="evento.Titolo" class="evento-img" />
          <div class="evento-content">
            <h2>{{ evento.Titolo }}</h2>
            <p class="data">{{ longStringDate(evento.Data) }}</p>
            <p class="descrizione">
              {{ evento.Descrizione.length == 180 ? evento.Descrizione + '...' : evento.Descrizione }}
            </p>
            <button class="btn-dettaglio">Vedi di più</button>
          </div>
        </a>
      </div>

      <div v-if="!hideDiv" class="loadMoreDiv">

        <button @click="loadMore()">
          <v-icon class="icon">mdi-arrow-down</v-icon>
          Carica altri
        </button>
      </div>
    </div>
  </main>
  <myFooter v-if="!error" />
  <myError v-if="error" :obj="errorObj" />
  <MyLoading :show="!error && !eventi && !paragraphs" />
</template>

<style scoped>

h1{
  color: rgba(84, 84, 84, 1);
  margin: 0;
}

h2{
  font-size: 38px;
}
span.paragraph {
  display: flex;
  flex-direction: column;
}

main.edit {
  cursor: default;
  margin: 0 !important;
}

.edit,
.changeText {
  cursor: pointer;
  transition: transform 0.3s, opacity 0.3s;
}

img.edit:hover,
.changeText:hover {
  opacity: 0.8;
  transform: scale(1.05);
}

.loadMoreDiv,
.loadMoreDiv button {
  display: flex;
  align-items: center;
  justify-content: center;
}

.loadMoreDiv {
  max-width: 200px;
}

.loadMoreDiv button {
  padding: 15px 25px !important;
  min-width: 150px;
  padding: 6px 12px;
  font-size: 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #a1864f;
  width: 50px;
  box-sizing: border-box;
  color: white;
  transition: background 0.3s;
}

.loadMoreDiv button:hover {
  background: #8c7540;
}

main {
  width: 100%;
  padding: 3.5vh 5vw;
  margin-top: 90px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  row-gap: 4vh;
  box-sizing: border-box;
  background: url("/img/paperBackground.png") repeat;
  background-size: auto;
}

hr {
  margin: 3vh 0;
}

.hero-section>img {
  position: absolute;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}

.eventi-container h2 {
  text-align: center;
}

.hero-section {
  position: relative;
  width: 100%;
  height: 380px;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  align-items: center;
  padding: 40px 0;
  justify-content: center;
}

.hero-section>* {
  z-index: 2;
}

.hero-overlay {
  background-color: rgba(0, 0, 0, 0.5);
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  box-sizing: border-box;
}

.hero-text,
:deep(.hero-text p) {
  color: white !important;
  text-align: center !important;
  max-width: 850px;
}

.hero-text .sottotitolo,
:deep(.hero-text .sottotitolo p) {
  text-transform: uppercase;
  font-size: 18px !important;
  margin-bottom: 10px;
  letter-spacing: 1px;
  color: #f4e4c1 !important;
}

.hero-text .titolo,
:deep(.hero-text .titolo p) {
  font-size: 38px;
  margin-bottom: 14px;
  color: #fff;
}

.hero-text .descrizione,
:deep(.hero-overlay > .hero-text > span.paragraph > p) {
  font-size: 18px;
  line-height: 1.6;
  color: #f0f0f0 !important;
}

.eventi-container {
  display: flex;
  flex-wrap: wrap;
  column-gap: 24px;
  row-gap: 8vh;
  width: 100%;
  justify-content: center;
}

.eventi-container>div,
#pastEvent>div {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  justify-content: center;
  gap: 24px;
  width: 100%;
  margin-bottom: 3.5vh;
}

#pastEvent {
  width: 100%;
  flex-direction: column;
  align-items: center;
}

.eventi-container h1 {
  width: 100%;
  margin: 0;
  margin-bottom: 3vh;
  text-align: center;
  font-size: 5vh;
}

.evento-card {
  display: flex;
  flex-direction: column;
  background: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.175);
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  text-decoration: none;
  flex: 1;
  color: initial;
  min-width: 32%;
  height: 500px;
  max-width: 50%;
  transition: transform 0.2s ease;
}

.evento-card:hover {
  transform: translateY(-4px);
}

.evento-img {
  aspect-ratio: 9 / 16;
  /* max-width: auto; */
  max-height: 55%;
  object-fit: cover;
}

.evento-content {
  display: flex;
  flex-direction: column;
  padding: 18px;
  height: 45%;
  justify-content: space-between;
  box-sizing: border-box;
}

.evento-content h2 {
  font-size: 21px;
  margin: 0 0 8px;
  color: #222;
}

.data {
  font-size: 14px;
  color: #777;
  margin-bottom: 10px;
}

.descrizione,
:deep(.hero-text span.paragraph p) {
  font-size: 15px;
  color: #444;
  line-height: 1.6;
  flex: 1;
  overflow: hidden;
  /* max-height: 30px; */
  margin-bottom: 8px;
}

.btn-dettaglio {
  align-self: flex-start;
  padding: 8px 14px;
  font-size: 14px;
  background-color: #800000;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-dettaglio:hover {
  background-color: #a00000;
}

#pastEvent .evento-card {
  max-width: 600px;
  min-width: 400px;
}

@media screen and (max-width: 1045px) {
  main {
    padding: 4vw 2.5vw;
  }

  .evento-card,
  #pastEvent .evento-card {
    min-width: 32%;
    height: 500px;
    max-width: 450px;
  }
}

@media screen and (max-width:680px) {

  .evento-card,
  #pastEvent .evento-card {
    max-width: 90%;
    min-width: 90%;
    min-height: 100%;
  }

  .descrizione {
    font-size: inherit;
  }
}

@media screen and (max-width: 768px) {

  .hero-section {
    height: auto;
    padding: 40px 0;
  }

  .hero-text .titolo,
  :deep(.hero-text .titolo p) {
    font-size: 28px;
  }

  .evento-content h2 {
    font-size: 18px;
  }

  .descrizione {
    font-size: 13px;
  }

  .hero-text .descrizione,
  :deep(.hero-text span.paragraph p) {
    font-size: 16px;
  }
}

@media (max-width:480px) {

  .hero-text .descrizione,
  :deep(.hero-text span.paragraph p) {
    font-size: 12px;
  }

  .evento-content h2 {
    font-size: 18px;
  }

  .data {
    margin: 0;
  }

  .eventi-container {
    row-gap: 5vh;
  }
}
</style>