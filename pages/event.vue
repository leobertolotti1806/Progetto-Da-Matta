<script setup>
import { getData, longStringDate, toSlashDate } from '~/assets/script';

const route = useRoute();

const error = ref(false);
const evento = ref(null);
const errorObj = reactive({
    title: "",
    msg: "",
    link: "",
    linkMsg: ""
});
const posti = ref(0);
useHead({ title: "Evento" });

if (!route.query.id) {
    useHead({ title: "Eventi" });
    error.value = true;
} else {
    getData("/getEvento?getPostiDisponibili&id=" + route.query.id).then(function (data) {
        if (!data.ok) {
            error.value = true;
            errorObj.msg = data.msg;
            errorObj.linkMsg = 'Ritorna agli eventi'
            errorObj.link = '/eventi';
        } else if (!data.evento) {
            error.value = true;
            errorObj.msg = "Evento non trovato!";
            errorObj.linkMsg = 'Ritorna agli eventi'
            errorObj.link = '/eventi';
        } else {
            evento.value = data.evento;
            posti.value = data.posti;
            useHead({ title: evento.value.Titolo });
        }
    });
}

function iscriviti() {
    window.location.href = "/iscrizione?id=" + route.query.id;
}

</script>

<template>
    <myHeader v-if="!error"></myHeader>
    <main v-if="!error && evento">
        <section class="hero-section">
            <img :src="`/img/event/${evento.Id}.webp?d=${Math.random()}`" :alt="evento.Titolo" />
            <div class="hero-text">
                <h1 class="titolo">{{ evento.Titolo }}</h1>
                <h2 class="sottotitolo">{{ longStringDate(evento.Data) }}</h2>
            </div>
        </section>

        <section class="view-section">
            <div class="evento-main">
                <h1>Più dettagli</h1>
                <p class="evento-subtitle">Dalle {{ evento.OraInizio }} alle {{ evento.OraFine }}</p>
                <p class="evento-descrizione" v-html="evento.Descrizione.replaceAll('\n', '<br>')"></p>

                <div class="evento-dettagli">
                    <h2>Ulteriori informazioni:</h2>
                    <ul>
                        <li><b>⏳ Termine iscrizione:</b> {{ longStringDate(evento.ScadenzaIscrizione) }}</li>
                        <li><b>📅 Posti totali:</b> {{ evento.PostiTotali }}</li>
                        <li>🗺️ <a
                                :href="`https://www.google.it/maps/place/${(evento.Indirizzo + ' ' + evento.Citta).replaceAll(' ', '+')}`"
                                target="_blank">
                                Guarda
                                {{ evento.Citta + ", " + evento.Indirizzo }} su maps!</a>
                        </li>
                        <li><b>💶 Costo:</b>
                            {{
                                evento.Costo > 0 ? evento.Costo + "€" : "GRATIS"
                            }}
                        </li>
                    </ul>
                </div>
            </div>
            <hr>
            <!-- Colonna destra: box info -->
            <div class="evento-sidebar">
                <div class="box-info">
                    <div class="info-item"><span>📅</span>
                        <div><b>DATA</b>{{ toSlashDate(evento.Data) }}</div>
                    </div>
                    <div class="info-item"><span>🕒</span>
                        <div><b>ORA</b>{{ evento.OraInizio }} - {{ evento.OraFine }}</div>
                    </div>
                    <div class="info-item"><span>💶</span>
                        <div><b>COSTO</b>
                            {{
                                evento.Costo > 0 ? evento.Costo + "€" : "GRATIS"
                            }}
                        </div>
                    </div>
                    <div class="info-item"><span>🎟️</span>
                        <div><b>Posti disponibili: </b><span style="font-size: 18px;">{{ posti }}</span></div>
                    </div>
                </div>

                <button @click="iscriviti()" class="btn-iscriviti">Iscriviti</button>
            </div>
        </section>
    </main>
    <MyFooter v-if="!error"></MyFooter>
    <myError v-if="error" :obj="errorObj" />
    <MyLoading :show="!error && !evento" />
</template>

<style scoped>
main {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    min-height: 100vh;
    padding: 4vh 6vw 10vh 6vw;
    row-gap: 10vh;
    margin-top: 90px;
    background: url("/img/triangleBackground.png") repeat;
    background-size: auto;
}

hr {
    display: none;
}

section,
main>div {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 3vw;
    width: 100%;
    flex: 1;
}

.hero-section {
    background: #f8f8f8;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    height: 430px;
    display: flex;
    justify-content: space-evenly;
    flex: 1 0 0;
    padding: 6vh 3vh;
    box-sizing: border-box;
}

.hero-section img {
    max-height: 100%;
    max-width: 60%;
    border-radius: 12px;
}

.hero-text {
    color: white;
    max-width: 90%;
    text-align: right;
}

.hero-text .titolo {
    font-size: 50px;
    color: #2c3e50;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.hero-text .sottotitolo {
    font-size: 18px;
    color: #777;
}



/* EVENTO PRINCIPALE */
.evento-main {
    background: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 2rem;
    width: 66%;
}

.evento-main h1 {
    font-size: 32px;
    margin-bottom: 0.25rem;
    color: #2c3e50;
}

.evento-subtitle {
    color: #777;
    font-style: italic;
    margin-bottom: 16px;
}

.evento-descrizione {
    font-size: 16px;
    line-height: 1.7;
    color: #333;
    margin-bottom: 32px;
}

.evento-dettagli h2 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.evento-dettagli ul {
    list-style: none;
    padding: 0;
    color: #444;
    line-height: 1.6;
}

/* SIDEBAR */
.evento-sidebar {
    width: 34%;
    background: #fff;
    padding: 1.5rem;
    border: 1px solid #eee;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    height: fit-content;
}

.box-info {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    gap: 20px;
    padding: 1vh;
    border-radius: 8px;
    align-items: center;
    background-color: #f7f7f7;
}

.info-item span {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 30px;
    width: 30px;
    font-size: 24px;
}

.info-item div {
    display: flex;
    flex-direction: column;
    gap: 0.25vh;
}

.info-item b {
    display: block;
    font-weight: bold;
    color: #444;
}

.btn-iscriviti {
    display: block;
    text-align: center;
    background: #a1864f;
    color: #fff;
    width: 100%;
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.3s;
}

.btn-iscriviti:hover {
    background: #8b723e;
}

/* RESPONSIVE */
@media screen and (max-width: 1024px) {
    main {
        padding: 4vh 3vw 10vh 3vw;
    }

    .hero-text .titolo {
        font-size: 38px;
    }

    .hero-text .sottotitolo {
        font-size: 16px;
    }
}

@media screen and (max-width: 768px) {
    section {
        flex-direction: column;
        row-gap: 5vh;
    }

    .view-section h1,
    .view-section .evento-subtitle,
    .evento-descrizione {
        text-align: center;
    }
    .evento-sidebar{
        padding: 2rem 1.5rem;
    }

    main {
        padding: 3vh 4vw 9vh 4vw;
        row-gap: 10vh;
    }

    .hero-section {
        width: 100%;
        min-height: fit-content;
        padding: 5vh 0;
    }

    .hero-section img {
        max-width: 85%;
        max-height: 350px;
    }

    .hero-text {
        text-align: center;
    }

    .hero-text .titolo {
        font-size: 30px;
    }

    .hero-text .sottotitolo {
        font-size: 16px;
    }

    .evento-main,
    .evento-sidebar {
        width: 100%;
    }

    .evento-dettagli {
        text-align: center;
    }

    hr {
        display: block;
    }
}

@media screen and (max-width: 480px) {
    .hero-text .titolo {
        font-size: 24px;
    }

    .hero-text .sottotitolo {
        font-size: 13px;
    }

    .hero-section img {
        max-width: 95%;
    }

    main {
        row-gap: 14vh;
    }
}
</style>