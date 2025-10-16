<script setup>
import { getData } from '~/assets/script';
const route = useRoute();

const error = ref(false);
const vino = ref(null);
const errorObj = reactive({
    title: "",
    msg: "",
    link: "",
    linkMsg: ""
});
useHead({ title: "Vino" });
if (!route.query.id) {
    error.value = true;
} else {
    getData("/getVino?id=" + route.query.id).then((data) => {
        if (!data.ok) {
            error.value = true;
            errorObj.msg = data.msg || "Errore durante il caricamento del vino.";
            errorObj.linkMsg = 'Torna alla lista vini';
            errorObj.link = '/vini';
        } else if (!data.vino) {
            error.value = true;
            errorObj.msg = "Vino non trovato!";
            errorObj.linkMsg = 'Torna alla lista vini';
            errorObj.link = '/vini';
        } else {

            vino.value = data.vino;
            useHead({ title: data.vino.Nome });
        }
    });
}
</script>

<template>
    <myHeader v-if="!error" />
    <main v-if="!error && vino">
        <section class="vino-container">
            <!-- IMMAGINE -->
            <div class="vino-img">
                <div class="etichetta" v-if="vino.Evidenzia">In Evidenza</div>
                <img :src="`/img/vini/${vino.Id}.webp?d=${Math.random()}`" :alt="vino.Nome" />
            </div>

            <div class="vino-main-info">
                <h1>{{ vino.Nome }}</h1>
                <h2>{{ vino.Marca }}</h2>

                <!-- Informazioni sintetiche -->
                <div class="vino-info-badge">
                    <span>{{ vino.Anno }}</span>
                    <span>{{ vino.Quantita }} L</span>
                    <span>{{ vino.Colore }}</span>
                    <span v-if="vino.Denominazione">{{ vino.Denominazione }}</span>
                    <span v-if="vino.Regione">{{ vino.Regione }}</span>
                </div>

                <p class="vino-descrizione" v-html="vino.Descrizione.replaceAll('\n', '<br>')"></p>

                <!-- PREZZO -->
                <div class="prezzo-wrapper">
                    <template v-if="vino.Offerta">
                        <span class="prezzo-originale">{{ vino.Costo }} €</span>
                        <span class="prezzo-offerta">{{ vino.Offerta }} €</span>
                    </template>
                    <template v-else>
                        <span class="prezzo-normale">{{ vino.Costo }} €</span>
                    </template>
                </div>

                <!-- BOTTONE -->
                <button class="btn-acquista">Passa da noi o contattaci per averlo!</button>
            </div>

            <hr>

            <!-- SCHEDA TECNICA -->
            <div class="scheda-tecnica">
                <h3>📋 Scheda tecnica</h3>
                <div class="scheda-grid">
                    <div><b>Gradazione</b><span>{{ vino.Gradazione ? vino.Gradazione + '°' : 'Non specificato'}}</span></div>
                    <div><b>Tipo</b><span>{{ vino.Effervescenza }}</span></div>
                </div>
            </div>
        </section>
    </main>
    <myFooter v-if="!error" />
    <myError v-if="error" :obj="errorObj" />
    <MyLoading :show="!error && !vino" />
</template>

<style scoped>
main {
    display: flex;
    justify-content: center;
    margin-top: 90px;
    padding: 4vh 6vw;
    background: url("/img/lightBackground.png") repeat ;
}

.vino-container {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}

/* IMMAGINE */
.vino-img {
    position: relative;
    background: #fafafa;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    flex: 1;
}

.vino-img img {
    max-width: 100%;
    max-height: 450px;
    height: 100%;
    object-fit: contain;
}

.etichetta {
  position: absolute;
  top: 12px;
  left: 12px;
  background-color: #d90000;
  color: white;
  padding: 5px 10px;
  font-size: 12px;
  border-radius: 6px;
  text-transform: uppercase;
  font-weight: bold;
}

/* INFO */
.vino-main-info {
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    gap: 12px;
    flex: 1;
    padding-bottom: 20px;
}

.vino-main-info h1 {
    font-size: 36px;
    color: #2c3e50;
}

.vino-main-info h2 {
    font-size: 20px;
    color: #777;
    font-weight: normal;
}

.vino-descrizione {
    font-size: 17px;
    flex: 0.8;
    line-height: 1.7;
    color: #333;
}

/* PREZZO */
.prezzo-wrapper {
    font-size: 22px;
    margin-top: 12px;
}

.prezzo-originale {
    text-decoration: line-through;
    color: #888;
    margin-right: 10px;
}

.prezzo-offerta {
    color: #a00000;
    font-weight: bold;
    font-size: 24px;
}

.prezzo-normale {
    color: #222;
    font-weight: bold;
}

hr {
    width: 100%;
}

/* BOTTONE */
.btn-acquista {
    background: #800000;
    color: white;
    border-radius: 8px;
    padding: 12px 20px;
    border: none;
    cursor: pointer;
    transition: background 0.3s;
    font-size: 16px;
    align-self: flex-start;
}

.btn-acquista:hover {
    cursor: default;
}

/* SCHEDA TECNICA */
.scheda-tecnica {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 12px;
    min-width: 100%;
}

.scheda-tecnica h3 {
    font-size: 18px;
    margin-bottom: 15px;
    color: #2c3e50;
}

.scheda-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.scheda-grid div {
    flex: 1;
    min-width: 120px;
    background: white;
    padding: 10px;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.scheda-grid b {
    font-size: 14px;
    color: #555;
}

.scheda-grid span {
    font-size: 16px;
    font-weight: bold;
    color: #222;
}

.vino-info-badge {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 5px 0 10px;
}

.vino-info-badge span {
    background: #f1f1f1;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    color: #444;
}

@media (max-width: 1024px) {
    main {
        padding: 4vh 3vw;
    }

    .vino-main-info h1 {
        font-size: 29px;
    }
}

/* MOBILE */
@media (max-width: 768px) {
    .vino-info-badge{
        justify-content: center;
    }
    .vino-container {
        flex-direction: column;
        width: 100%;
    }

    .vino-descrizione{
        margin: 24px 0;
    }

    hr {
        width: 70%;
        align-self: start;
    }

    .vino-container>* {
        width: 100%;
    }

    .vino-main-info {
        text-align: center;
        justify-content: space-between;
        padding-bottom: 0;
    }

    .vino-main-info h1 {
        font-size: 28px;
    }

    .vino-main-info h2 {
        font-size: 18px;
    }

    .btn-acquista {
        align-self: center;
    }

    .vino-img img{
        height: 400px;
    }
}
</style>
