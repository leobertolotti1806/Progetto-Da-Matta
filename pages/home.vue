<script setup>
import { getData, getIdKeyAsArrayKey } from '~/assets/script';

const props = defineProps({
  images: {
    Type: Array, default: [
      "/img/home/home0.webp",
      "/img/home/home1.webp",
      "/img/home/home2.webp",
      "/img/home/home3.webp",
      "/img/home/home4.webp"
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

getData("/getParagraphs?page=home").then(function (esito) {
  if (!esito.ok) {
    error.value = true;
    errorObj.msg = esito?.msg;
  } else {
    paragraphs.value = getIdKeyAsArrayKey(esito.paragrafi);
  }
});
useHead({ title: "Chi Siamo" });

useHead({ title: "Home" });
</script>

<template>
  <myHeader v-if="!props.editMode && !error" />
  <main v-if="!error && paragraphs" :class="props.editMode ? 'edit' : null">
    <section :class="props.editMode ? 'edit' : null" id="section1"
      @dblclick="props.editMode && $emit('changeImage', 0)">
      <img :src="props.images[0]" alt="Immagine non trovata">
      <div>
        <h1 :class="props.editMode ? 'changeText' : null" @click="props.editMode && $emit('changeParagraph', 22)"
          v-html="paragraphs[22]"></h1>
        <p :class="props.editMode ? 'changeText' : null" @click="props.editMode && $emit('changeParagraph', 23)"
          v-html="paragraphs[23]"></p>
        <a href="#about">
          <v-icon class="icon">mdi-arrow-down</v-icon>
        </a>
      </div>
    </section>
    <section id="about">
      <div class="divText">
        <h2 :class="props.editMode ? 'changeText' : null" @click="props.editMode && $emit('changeParagraph', 24)"
          v-html="paragraphs[24]"></h2>
        <span class="paragraph" :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 25)" v-html="paragraphs[25]"></span>

        <a class="btn" href="/about">Scopri di più su di noi</a>
      </div>
      <div class="divImg">
        <img :class="props.editMode ? 'edit' : null" :src="props.images[1]"
          @dblclick="props.editMode && $emit('changeImage', 1)" alt="Immagine non trovata">
        <img :class="props.editMode ? 'edit' : null" :src="props.images[2]"
          @dblclick="props.editMode && $emit('changeImage', 2)" alt="Immagine non trovata">
      </div>
    </section>
    <section id="wines">
      <div class="divImg">
        <img :class="props.editMode ? 'edit' : null" :src="props.images[3]"
          @dblclick="props.editMode && $emit('changeImage', 3)">
      </div>
      <div class="divText">
        <h2 id="titleWines" :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 26)" v-html="paragraphs[26]"></h2>
        <span class="paragraph" :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 27)" v-html="paragraphs[27]"></span>
        <a class="btn" href="/vini">Vedi i nostri vini</a>
      </div>
    </section>
    <section id="eventi">
      <img :class="props.editMode ? 'edit' : null" :src="props.images[4]" alt="Immagine non trovata"
        @dblclick="props.editMode && $emit('changeImage', 4)">
      <div class="divText">
        <h2 :class="props.editMode ? 'changeText' : null" @click="props.editMode && $emit('changeParagraph', 28)"
          v-html="paragraphs[28]"></h2>
        <span class="paragraph" :class="props.editMode ? 'changeText' : null"
          @click="props.editMode && $emit('changeParagraph', 29)" v-html="paragraphs[29]"></span>
        <hr>
        <a class="btn" href="/eventi">Vedi i nostri eventi</a>
      </div>
      <img :class="props.editMode ? 'edit' : null" :src="props.images[4]" alt="Immagine non trovata"
        @dblclick="props.editMode && $emit('changeImage', 4)">
    </section>
  </main>
  <myFooter v-if="!error"></myFooter>
  <myError v-if="error" :obj="errorObj" />
  <MyLoading :show="!error && !paragraphs" />
</template>

<style scoped>
main {
  display: flex;
  flex-direction: column;
  width: 100%;
  background: url("/img/paperBackground.png") repeat;
  background-color: #fdfdfd;
}

section h2 {
  margin-bottom: 1vh;
}

section span.paragraph {
  display: flex;
  flex-direction: column;
}

.edit,
.changeText {
  cursor: pointer;
  transition: transform 0.3s, opacity 0.3s;
}

main.edit {
  cursor: default;
  margin: 0 !important;
}

img.edit:hover,
.changeText:hover {
  opacity: 0.8;
  transform: scale(1.05);
}

section.edit:hover {
  opacity: 0.8;
}

section.edit {
  margin: 0 !important;
  height: 100vh !important;
}

section {
  display: flex;
  box-sizing: border-box;
  width: 100%;
  height: 100vh;
  padding: 4vw;
  max-height: 100vh;
}

section .btn {
  width: fit-content;
}

#section1 a {
  text-decoration: none;
  font-size: 25px;
  margin-top: 20px;
  color: #fff;
}

#section1,
:deep(#section1 p) {
  color: #fff;
}

#section1>div {
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  background-color: rgba(240, 248, 255, 0.2);
  padding: 3.5vw 7vw;
  box-shadow: 0px 0px 15px 4px rgba(240, 248, 255, 0.3);
}

:deep(#section1>div>*) {
  text-shadow: 0px 2px 10px rgba(0, 0, 0, 0.8);
}

:deep(#section1 h1 p) {
  font-size: 70px;
  margin: 0;
}

#section1 {
  position: relative;
  text-align: center;
  margin-top: 90px;
  height: calc(100vh - 90px);
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

#section1>img {
  position: absolute;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

:deep(h2 p) {
  font-size: 32px;
}

:deep(.divText p) {
  margin: 20px 0;
}

#about,
#wines {
  align-items: center;
  justify-content: center;
}

.divText,
.divImg {
  display: flex;
  width: 50%;
}

.divText {
  justify-content: center;
  flex-direction: column;
  gap: 24px;
  padding-right: 4vw;
  box-sizing: border-box;
}

.divText span.paragraph {
  gap: 20px;
}

.divImg {
  align-items: center;
  justify-content: space-evenly;
  height: fit-content;
  gap: 1vw;
  box-sizing: border-box;
  flex-wrap: wrap;
}

.divImg img {
  max-width: 45%;
  min-width: 250px;
  height: fit-content;
  border-radius: 12px;
  box-shadow:
    rgba(0, 0, 0, 0.25) 0 7vh 7vh,
    rgba(0, 0, 0, 0.12) 0 -1.5vh 4vh,
    rgba(0, 0, 0, 0.12) 0 0.5vh 0.8vh,
    rgba(0, 0, 0, 0.17) 0 1.6vh 1.8vh,
    rgba(0, 0, 0, 0.09) 0 -0.4vh 0.6vh;
}

#wines {
  text-align: right;
}

#wines>.divText {
  align-items: center;
  padding-right: 0;
  padding-left: 4vw;
}

#wines>.divImg img {
  max-width: 100%;
}

#eventi {
  align-items: center;
  justify-content: space-evenly;
}

#eventi>.divText {
  text-align: center;
  align-items: center;
  padding: 4vw;
  width: 60%;
}

#eventi>img {
  max-width: 18%;
  height: fit-content;
  max-height: 80%;
}


#eventi>.divText>p:last-of-type {
  margin-bottom: 0;
}


@media screen and (max-width: 768px) {
  section {
    flex-direction: column;
  }

  main {
    row-gap: 6vh;
  }

  #about .divText,
  #wines .divText {
    align-items: center;
    text-align: center;
    height: 80%;
    width: 100%;
    padding: 0 !important;
  }

  #eventi>img {
    max-width: 15%;
    min-width: 15%;
    height: fit-content;
    max-height: 80%;
  }

  #eventi {
    flex-direction: row;
    justify-content: space-between;
    gap: 2vw;
  }

  #eventi .divText {
    padding: 0;
    height: 80%;
  }

  span.paragraph {
    gap: 0 !important;
  }

  .divImg {
    width: 100%;
    height: 45%;
    justify-content: space-between;
  }

  #wines .divImg {
    justify-content: center;
  }

  .divImg img {
    max-width: 45%;
    min-width: 200px;
    max-height: 100%;
    height: fit-content;
    box-shadow: rgba(0, 0, 0, 0.19) 0px 10px 20px, rgba(0, 0, 0, 0.23) 0px 6px 6px;
  }

  #wines .divImg img {
    max-width: 95%;
  }

  #wines {
    padding-bottom: 0;
  }

  #eventi {
    padding-top: 0;
  }

  :deep(#section1 h1 p) {
    font-size: 52.5px;
  }

  :deep(section h2 p) {
    font-size: 25px;
  }
}

@media screen and (max-width: 480px) {
  .divImg {
    justify-content: space-evenly;
    height: 25%;
  }

  :deep(#section1 h1 p) {
    font-size: 40px;
  }

  :deep(p) {
    font-size: 15px;
  }

  .btn {
    padding: 8px 20px
  }

  .divText {
    gap: 0;
    height: 100%;
    justify-content: space-between;
  }

  :deep(#about h2 p) {
    margin: 0;
  }

  #wines {
    justify-content: flex-start;
  }

  #wines .divImg {
    height: 30%;
  }

  #wines .divText {
    justify-content: flex-end;
  }

  #about .divImg {
    height: 35%;
    align-items: flex-end;
  }

  #eventi {
    gap: 10px;
  }

  .divImg img {
    max-width: 40%;
    min-width: 80px;
    transform: scale(0.9);
  }

  :deep(.divText p) {
    margin: 12px 0;
  }

  #about,
  #wines,
  #eventi {
    max-height: unset;
    min-height: 90vh;
  }


  .btn {
    min-height: 40px;
  }
}

@media screen and (max-width: 370px) {
  .divText {
    min-height: 60vh;
  }

  :deep(.divText p) {
    margin: 5px 0;
  }

  .btn {
    transform: scale(0.9);
  }
}

@media screen and (max-height: 700px) {
  #about,
  #wines,
  #eventi {
    max-height: unset;
    min-height: 110vh !important;
    margin: 5vh 0;
  }

  .divText{
    min-height: 80vh ;
  }
}

@media screen and (max-height: 560px) {
  #about,
  #wines,
  #eventi {
    
    margin: 20vh 0;
  }
}

.btn {
  position: relative;
  overflow: hidden;
  z-index: 1;
}

.btn::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 0;
  height: 100%;
  background-color: #ad3523;
  /* colore dell'hover */
  z-index: -1;
  transition: width 0.2s ease-in;
}

.btn:hover::before {
  width: 100%;
}
</style>