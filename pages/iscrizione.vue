<script setup>
import { getData, longStringDate, postData } from '~/assets/script';

const route = useRoute();
const error = ref(!route.query.id);
const errorObj = reactive({
    title: "",
    msg: "",
    link: "",
    linkMsg: ""
});
const evento = ref(null);
const formData = reactive({
    nome: '',
    cognome: '',
    cellulare: '',
    privacy: false
});

const showPrivacy = ref(false);
useHead({ title: "Iscrizione" });
if (!error.value) {
    getData("/getEvento?getPostiDisponibili&minimal&id=" + route.query.id).then(function (data) {
        if (!data.ok) {
            error.value = true;
            errorObj.msg = data.msg;
        } else {
            useHead({ title: "Iscrizione a " + data.evento.Titolo });
            const today = new Date().toISOString().split("T")[0];
            evento.value = data.evento;

            if (data.posti < 1) {
                error.value = true;
                errorObj.title = "Sold out!";
                errorObj.msg = "Ci dispiace ma non ci sono più posti disponibili!";
                errorObj.linkMsg = "Vai agli eventi";
                errorObj.link = "/eventi";

            } else if (today > evento.value.Data) {
                error.value = true;
                errorObj.title = "Troppo tardi!";
                errorObj.msg = "Ci dispiace ma non ci si può iscrivere ad un evento passato";
                errorObj.linkMsg = "Vai agli eventi";
                errorObj.link = "/eventi";
            } else if (
                today > evento.value.ScadenzaIscrizione ||
                today > evento.value.Data
            ) {
                error.value = true;
                errorObj.title = "Troppo tardi!";
                errorObj.msg = "Ci dispiace ma il termine delle iscrizioni è già passato!";
                errorObj.linkMsg = "Vai agli eventi";
                errorObj.link = "/eventi";
            }
        }
    });
}

async function invia() {
    if (!formData.privacy) {
        alert("Devi accettare l'informativa privacy per procedere.");
        return;
    }

    const params = {
        idEvento: route.query.id,
        cellulare: formData.cellulare,
        cognome: formData.cognome,
        nome: formData.nome
    };

    let esito = await postData("/iscrivi", params);

    if (!esito.ok) {
        error.value = true;
        errorObj.msg = esito.msg;
        errorObj.link = "/iscrizione?id=" + route.query.id;
        errorObj.linkMsg = "Riprova ad iscriverti";
    } else if (esito.paymentAccepted || esito.alreadySubscribed) {
        error.value = true;
        errorObj.msg = esito.msg;
        errorObj.title = "Già iscritto!";
    } else if (esito.redirectTo == "/success") {
        error.value = true;
        errorObj.msg = "Iscrizione avvenuta con successo!\nÈ stato riservato un posto per te per " + evento.value.Titolo + " in data " + longStringDate(evento.value.Data);
        errorObj.title = "Ottimo!";
    } else {
        window.location.href = esito.redirectTo + "?id=" + route.query.id;
    }
}
</script>

<template>
    <MyLoading />
    <myHeader v-if="!error"></myHeader>
    <main v-if="!error">
        <section>
            <h1>Iscriviti a {{ evento?.Titolo }}</h1>

            <form @submit.prevent="invia()">
                <myInput label="Nome" v-model="formData.nome" required />
                <myInput label="Cognome" v-model="formData.cognome" required />
                <myInput label="Cellulare" v-model="formData.cellulare" type="tel" required />
                <!-- Campo privacy con myInput -->
                <div class="privacy">
                    <myInput type="checkbox" :placeholder="'Ho letto e accetto l’informativa privacy relativa all’iscrizione '
                        + (evento?.Costo > 0 ? 'e al pagamento' : '') + ' dell’evento'" required
                        v-model="formData.privacy" />
                    <a href="#" @click.prevent="showPrivacy = true" class="privacy-link">
                        Leggi informativa
                    </a>
                </div>
                <div>
                    <p class="costo">
                        Costo: &nbsp;<b>{{ evento?.Costo > 0 ? evento?.Costo + '€' : 'GRATIS' }}</b>
                    </p>
                    <button v-if="evento?.Costo == 0" type="submit" class="btn">Iscriviti</button>
                    <button v-else class="btn btn-satispay" type="submit">
                        Satispay
                        <img src="/satispay.jpg" alt="Satispay logo">
                    </button>
                </div>
            </form>
        </section>
        <MyFooter></MyFooter>
    </main>
    <myModalPrivacy v-if="!error" :show="showPrivacy" :pagamenti="evento?.Costo > 0" @close="showPrivacy = false" />
    <myError v-if="error" :obj="errorObj"></myError>
    <MyLoading :show="!error && !evento" />
</template>

<style scoped>
:deep(.custom-checkbox) {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.privacy {
    display: flex;
    flex-direction: column;
}

.privacy-link {
    font-size: 14px;
    margin-top: 4px;
    color: blue;
    text-decoration: underline;
    cursor: pointer;
}

main {
    display: flex;
    flex-direction: column;
    align-items: center;
    max-height: 100vh;
    height: 100vh;
    row-gap: 4vh;
}

section {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    row-gap: 3vh;
    width: 100%;
    max-width: 860px;
    margin-top: 90px;
    padding: 3vh 6vw;
    min-width: 20%;
}

section>div {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

h1 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 8px;
}

b {
    font-size: 23px;
}

.costo {
    font-size: 16px;
    color: #333;
    margin-bottom: 20px;
    text-align: right;
}

form {
    display: flex;
    flex-direction: column;
    row-gap: 3vh;
}

.btn {
    text-align: center;
    padding: 2vh 0;
    border: none;
    background: #800000;
    width: 100%;
    color: white;
    font-weight: bold;
    font-size: 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.3s;
}

.btn-satispay {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ff3d00;
    padding: 1vh 0;
    column-gap: 8px;
}

.btn:hover {
    transform: scale(1.03);
}

@media screen and (max-width: 768px) {
    h1 {
        font-size: 22px;
    }

    main {
        min-height: 118vh;
    }
}

@media screen and (max-width:480px) {
    .btn {
        font-size: 15px;
    }

    main {
        min-height: 137vh;
    }
}
</style>
