<script setup>
import { getData } from '~/assets/script';
import QRCode from 'qrcode'

const route = useRoute();
const errorObj = reactive({
    title: "",
    msg: "",
    link: "",
    linkMsg: ""
});
const error = ref(false);

const qrCodeUrl = ref(null), paymentLink = ref(null), evento = ref(null);

error.value = false;

getData("/getPayment").then(function (data) {
    if (!data.ok) {
        error.value = true;
        errorObj.msg = data.msg;

        if (data.tryAgain) {
            errorObj.link = "/iscrizione?id=" + route.query.id;
            errorObj.linkMsg = "Riprova qui!";
        }
    } else if (data.paymentAccepted) {
        error.value = true;
        errorObj.title = "Ottimo!";
        errorObj.msg = data.msg;
    } else {
        const linkWithFixedImport = "https://www.satispay.com/download/qrcode/" + data.codeIdentifier;

        QRCode.toDataURL(linkWithFixedImport)
            .then(url => {
                qrCodeUrl.value = url;
            })
            .catch(err => {
                console.error("Errore nella generazione del qr code : ", err);
            });

        paymentLink.value = linkWithFixedImport;
    }
});

// Esegui polling ogni 3 secondi per verificare stato pagamento
onMounted(() => {
    if (!error.value) {
        const interval = setInterval(async function () {
            if (error.value) {
                clearInterval(interval);
            }

            let esito = await getData("/checkPayment");

            if (!esito.ok) {
                clearInterval(interval);
                error.value = true;

                errorObj.msg = esito.msg;
                errorObj.link = "/iscrizione?id=" + route.query.id;
                errorObj.linkMsg = "Riprova ad iscriverti";
            } else {
                if (esito.status === "ACCEPTED") {
                    clearInterval(interval);
                    error.value = true;
                    errorObj.msg = "Pagamento avvenuto con successo!\nÈ stato riservato un posto per te!";
                    errorObj.title = "Ottimo!";
                } else if (esito.status === "CANCELED" || esito.status === "EXPIRED") {
                    clearInterval(interval);
                    error.value = true;
                    errorObj.title = "Mancava poco!";
                    errorObj.msg = "Il pagamento è scaduto, riprova a rifarlo. (non è stato addebitato nulla)";

                    if (esito.tryAgain) {
                        errorObj.link = "/iscrizione?id=" + route.query.id;
                        errorObj.linkMsg = "Riprova qui!";
                    }
                }
            }
        }, 3000);
    }
});

</script>
<template>
    <myLoading :show="!error && !qrCodeUrl" />
    <main v-if="!error">
        <div class="container">
            <h1>Paga con Satispay</h1>
            <p class="subtitle">
                Manca poco per poter completare l'iscrizione!
                <br><br>
                Scansiona il QR code o clicca sul link per procedere:
            </p>

            <div class="qr-wrapper">
                <img :src="qrCodeUrl" alt="QR Code Satispay, se vedi questa scritta usa il link sotto per pagare"
                    class="qr-img" />
            </div>

            <a :href="paymentLink" target="_blank" class="btn-satispay">
                Apri in Satispay
                <img src="/satispay.jpg" alt="Satispay logo">
            </a>

            <p class="info">Se non riesci ad aprire satispay dal bottone, scannerizza il codice Qr con l'app.</p>
            <p class="info">Se ritorni su questa pagina dopo il pagamento avrai una conferma dell'iscrizione.</p>
        </div>
    </main>
    <myError v-else :obj="errorObj"></myError>
</template>

<style scoped>
main {
    display: flex;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.container {
    max-width: 450px;
    margin: 60px 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    row-gap: 3vh;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    background: #ffffff;
    text-align: center;
}

h1 {
    font-size: 28px;
    color: #333;
}

b {
    font-size: 17px;
    color: #333;
}

.subtitle {
    font-size: 16px;
    color: #555;
}

.qr-wrapper {
    width: 300px;
    height: 300px;
    margin: 1vh 0;
}

.qr-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.pay-link {
    display: inline-block;
    background: #a1864f;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 16px;
    transition: background 0.3s;
}

.pay-link:hover {
    background: #8c7540;
}

.info {
    font-size: 14px;
    color: #666;
}

@media (max-width: 600px) {
    .container {
        margin: 30px 16px;
        padding: 16px;
    }

    h1 {
        font-size: 22px;
    }

    .qr-wrapper {
        width: 220px;
        height: 220px;
    }

    .pay-link {
        font-size: 14px;
        padding: 10px 16px;
    }
}
</style>