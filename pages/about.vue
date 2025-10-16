<script setup>
import { getData, getIdKeyAsArrayKey } from '~/assets/script';

const props = defineProps({
  images: {
    Type: Array, default: [
      "/img/about/about0.webp",
      "/img/about/about1.webp",
      "/img/about/about2.webp",
      "/img/about/about3.webp",
      "/img/about/about4.webp"
    ]
  },
  editMode: false
});

defineEmits(["changeImage", "changeParagraph"]);

const error = ref(false);
const errorObj = reactive({
  title: "",
  msg: "",
  link: "",
  linkMsg: ""
});

const paragraphs = ref([]);

getData("/getParagraphs?page=about").then(function (esito) {
  if (!esito.ok) {
    error.value = true;
    errorObj.msg = esito?.msg;
  } else {
    paragraphs.value = getIdKeyAsArrayKey(esito.paragrafi);
  }
});
useHead({ title: "Chi Siamo" });
</script>

<template>
  <myHeader v-if="!error && !props.editMode" />

  <main v-if="!error && paragraphs" class="about" :class="props.editMode ? 'edit' : null">

    <!-- INTRO -->
    <section class="hero">
      <img :class="props.editMode ? 'edit' : null" :src="props.images[0]" alt="Immagine"
        @dblclick="props.editMode && $emit('changeImage', 0)">

      <h1 :class="props.editMode ? 'changeText' : null"
        @click="props.editMode && $emit('changeParagraph', 11)" v-html="paragraphs[11]">
        </h1>

      <span class="paragraph" :class="props.editMode ? 'changeText' : null"
        @click="props.editMode && $emit('changeParagraph', 1)" v-html="paragraphs[1]">

      </span>
    </section>

    <!-- CHI SIAMO -->
    <section class="block">
      <div class="text">
        <h2 :class="props.editMode ? 'changeText' : null"
        @click="props.editMode && $emit('changeParagraph', 12)" v-html="paragraphs[12]"></h2>
        <span class="paragraph" :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 2)" v-html="paragraphs[2]">
          
        </span>
      </div>
      <div class="image">
        <img :class="props.editMode ? 'edit' : null" :src="props.images[1]" alt="Immagine"
          @dblclick="props.editMode && $emit('changeImage', 1)">
      </div>
    </section>

    <!-- COSA FACCIAMO -->
    <section class="block">
      <div class="image">
        <img :class="props.editMode ? 'edit' : null" :src="props.images[2]" alt="Immagine"
          @dblclick="props.editMode && $emit('changeImage', 2)">
      </div>
      <div class="text">
        <h2 :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 13)" v-html="paragraphs[13]">
          </h2>
        <span class="paragraph" :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 3)" v-html="paragraphs[3]">
          
        </span>
      </div>
    </section>


    <!-- VALORI -->
    <section class="values">
      <h2 :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 14)" v-html="paragraphs[14]"></h2>
      <span class="paragraph" :class="props.editMode ? 'changeText' : null"
        @click="props.editMode && $emit('changeParagraph', 4)" v-html="paragraphs[4]">
       
      </span>
    </section>

    <!-- PERCHE' SCEGLIERCI (CARDS) -->
    <section class="cards">
      <h2 :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 15)" v-html="paragraphs[15]"></h2>
      <div class="card-list">
        <div class="card">
          <h3 :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 19)" v-html="paragraphs[19]"></h3>

          <span class="paragraph" :class="props.editMode ? 'changeText' : null"
            @click="props.editMode && $emit('changeParagraph', 5)" v-html="paragraphs[5]">
          </span>
        </div>
        <div class="card">
          <h3 :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 20)" v-html="paragraphs[20]"></h3>

          <span class="paragraph" :class="props.editMode ? 'changeText' : null"
            @click="props.editMode && $emit('changeParagraph', 6)" v-html="paragraphs[6]">
           
          </span>
        </div>
        <div class="card">
          <h3 :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 21)" v-html="paragraphs[21]"></h3>
          <span class="paragraph" :class="props.editMode ? 'changeText' : null"
            @click="props.editMode && $emit('changeParagraph', 7)" v-html="paragraphs[7]">
          
          </span>
        </div>
      </div>
    </section>


    <!-- COLLABORAZIONI -->
    <section class="block">
      <div class="text">
        <h2 :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 16)" v-html="paragraphs[16]"></h2>
        <span class="paragraph" :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 8)" v-html="paragraphs[8]">
          
        </span>
      </div>
      <div class="image">
        <img :class="props.editMode ? 'edit' : null" :src="props.images[3]" alt="Immagine"
          @dblclick="props.editMode && $emit('changeImage', 3)">
      </div>
    </section>

    <!-- REGALI -->
    <section class="block reverse">
      <div class="image">
        <img :class="props.editMode ? 'edit' : null" :src="props.images[4]" alt="Immagine"
          @dblclick="props.editMode && $emit('changeImage', 4)">
      </div>
      <div class="text">
        <h2 :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 17)" v-html="paragraphs[17]"></h2>
        <span class="paragraph" :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 9)" v-html="paragraphs[9]">
        </span>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta">
      <h2 :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 18)" v-html="paragraphs[18]"></h2>
      <span class="paragraph" :class="props.editMode ? 'changeText' : null"
        @click="props.editMode && $emit('changeParagraph', 10)" v-html="paragraphs[10]">
       
      </span>
      <a class="btn" href="/eventi" style="margin-top: 12px;">Scopri gli eventi</a>
    </section>

  </main>

  <MyFooter v-if="!error"></MyFooter>
  <myError v-if="error" :obj="errorObj" />
  <MyLoading :show="!error && !paragraphs" />
</template>


<style scoped>
main {
  margin-top: 90px;
  background: url("/img/lightBackground.png") repeat;
  background-color: #fdfdfd;
  display: flex;
  flex-direction: column;
  row-gap: 4vh;
}

main.edit {
  cursor: default;
  margin: 0 !important;
}

/* HERO */
.hero {
  position: relative;
  min-height: 40vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  color: #fff;
  padding: 2rem;
  overflow: hidden;
}

.hero>img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  z-index: 0;
}

.hero>* {
  z-index: 2;
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

section.edit:hover {
  opacity: 0.8;
}

:deep(p) {
  margin: 20px 0;
}

:deep(h3 p){
  font-size: 20px;
  color: black;
}

:deep(.hero h1 p){
  max-width: 800px;
  font-size: 42px;
  margin-bottom: 10px;
  text-shadow: 0 2px 6px rgba(0, 0, 0, .6);
}

:deep(.hero p) {
  max-width: 800px;
  color: #fff;
  text-shadow: 0 2px 6px rgba(0, 0, 0, .6);
}

/* BLOCCHI */
.block {
  display: flex;
  flex-wrap: wrap;
  gap: 4vw;
  align-items: center;
  padding: 6vh 6vw;
}

section span.paragraph {
  display: flex;
  flex-direction: column;
}

section>div {
  display: flex;
  align-items: center;
  justify-content: center;
}

.block.reverse {
  flex-direction: row-reverse;
}

.text {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 280px;
}

.image {
  flex: 1;
  display: flex;
  align-items: center;
  min-width: 280px;
}

.image img {
  align-self: flex-end;
  max-width: 100%;
  max-height: 500px;
  width: auto;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

/* VALUES */
.values {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 8vh 8vw;
}

.values::before {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom,
      transparent 0%,
      rgba(0, 0, 0, 0.045) 10%,
      rgba(0, 0, 0, 0.045) 90%,
      transparent 100%);
  pointer-events: none;
}

:deep(h2 p){
  font-size: 32px;
}

.values h2 {
  margin: 1vh 0;
}

:deep(.values span p) {
  max-width: 830px;
}

/* CARDS */
.cards {
  padding: 8vh 8vw;
  text-align: center;
}

.card-list {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
  justify-content: center;
  margin-top: 4vh;
}

.card {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 225px;
  max-width: 290px;
  min-height: 280px;
  max-height: fit-content;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
  transition: transform .2s;
  cursor: default;
}

.card:nth-child(1) {
  background: linear-gradient(135deg, #ffffff 0%, #fefce8 100%);
}

.card:nth-child(2) {
  background: linear-gradient(135deg, #ffffff 0%, rgba(240, 249, 255, 0.7) 100%);
}

.card:nth-child(3) {
  background: linear-gradient(135deg, #ffffff 0%, #fdf2f8 100%);
}

:deep(.card p) {
  margin-bottom: 0;
}

.card span.paragraph {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card:hover {
  transform: translateY(-5px);
}

/* CTA */
.cta {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 6vh 2vw;
  padding-bottom: 10vh;
  background: #fafafa;
}

.btn {
  background: #800000;
  color: #fff;
  padding: 10px 20px;
  border-radius: 8px;
  text-decoration: none;
  transition: background .2s;
}

.btn:hover {
  background: #a00000;
}

@media (max-width: 1024px) {
  :deep(p) {
    font-size: 18px;
  }

  .card {
    padding: 1.8rem;
    height: 300px;
  }
}

@media (max-width: 768px) {
  .hero h1 {
    font-size: 28px;
  }

  .block {
    row-gap: 5vh;
    flex-direction: column;
    text-align: center;
  }

  section {
    padding: 6vh 4vw !important;
  }

  .image img {
    max-width: 90%;
  }

  .card {
    padding: 1.5rem;
    /* max-width: none; */
    min-width: 250px;
    height: 250px;
  }
}

@media (max-width: 480px) {
  :deep(h2 p) {
    font-size: 28px;
  }

  :deep(p) {
    font-size: 16px;
  }
}
</style>
