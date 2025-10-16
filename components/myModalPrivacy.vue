<script setup>
import { getData, getIdKeyAsArrayKey } from '~/assets/script';

const paragraphs = ref([]);
const emit = defineEmits(['close']);

const props = defineProps({
  show: { type: Boolean, default: false },
  pagamenti: { type: Boolean, default: false }
});

getData("/getParagraphs?page=footer").then(function (esito) {
  if (!esito.ok) {
    alert("Ricarica la pagina");
    return;
  } else {
    paragraphs.value = getIdKeyAsArrayKey(esito.paragrafi);
  }
});
</script>

<template>
  <div v-if="show" class="modal-overlay" @click.self="emit('close')">
    <div>
      <button class="modal-close" @click="emit('close')">✖</button>
      <div class="modal-content">
        <header class="modal-header">
          <h2>Informativa Privacy</h2>
        </header>
        <div class="modal-body">
          <h3>Titolare del trattamento</h3>
          <p>{{ paragraphs[33] }} - {{ paragraphs[38] }} - {{ paragraphs[35] }} - {{ paragraphs[34] }}</p>

          <h3>Dati raccolti</h3>
          <p>Per l’iscrizione agli eventi vengono richiesti:</p>
          <ul>
            <li>Nome</li>
            <li>Cognome</li>
            <li>Numero di cellulare</li>
          </ul>

          <h3>Finalità del trattamento</h3>
          <p>I dati sono utilizzati esclusivamente per:</p>
          <ul>
            <li>Gestire le iscrizioni agli eventi</li>
            <li>Contattare gli iscritti per comunicazioni relative all’evento</li>
          </ul>

          <h3>Base giuridica</h3>
          <p>Il trattamento è necessario per l’esecuzione del servizio richiesto (art. 6.1.b GDPR).</p>

          <h3>Modalità di trattamento</h3>
          <p>I dati sono trattati solo dal titolare e non vengono condivisi con terzi.
            Sono memorizzati in formato elettronico, con misure di sicurezza adeguate.</p>

          <h3>Conservazione</h3>
          <p>I dati saranno conservati di norma per un massimo di 12 mesi dalla conclusione dell’evento
            e comunque per il tempo strettamente necessario alla gestione delle iscrizioni.
            Successivamente verranno cancellati o resi anonimi.</p>

          <h3 v-if="props.pagamenti">Cookie tecnici necessari</h3>
          <p v-if="props.pagamenti">
            Questo sito utilizza esclusivamente cookie tecnici indispensabili al corretto funzionamento del sistema di
            iscrizione e pagamento.
            In particolare, viene salvato un identificativo cifrato (payment id) che consente di associare
            il dispositivo alla richiesta di pagamento tramite Satispay.
            Senza questo cookie il servizio non può funzionare correttamente.
            Il cookie non contiene dati personali identificativi e viene eliminato automaticamente al termine della
            sessione o entro breve tempo dalla conclusione del pagamento.
            Non vengono utilizzati cookie di profilazione o di tracciamento.
          </p>


          <h3 v-if="props.pagamenti">Pagamenti</h3>
          <p v-if="props.pagamenti">I pagamenti online sono gestiti tramite Satispay, che tratta i dati di pagamento
            come titolare autonomo.
            Noi non raccogliamo né conserviamo dati relativi ai mezzi di pagamento.
            Riceviamo solo le informazioni necessarie per confermare il pagamento.
            <a href="https://www.satispay.com/it-it/legal-hub/privacy/" target="_blank" rel="noopener">Informativa
              Satispay</a>.
          </p>

          <h3>Diritti dell’interessato</h3>
          <p>Puoi chiedere in qualsiasi momento l’accesso, la rettifica o la cancellazione dei dati scrivendo a
            {{ paragraphs[35] }}
            o al {{ paragraphs[34] }}
            È inoltre possibile proporre reclamo al Garante per la protezione dei dati personali
            (<a href="https://www.garanteprivacy.it" target="_blank" rel="noopener">www.garanteprivacy.it</a>).</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay>div {
  background: white;
  padding: 20px 20px 0 20px;
  max-width: 800px;
  border-radius: 10px;
  width: 90%;
  position: relative;
  height: 90vh;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  width: 100%;
  height: calc(90vh - 40px);
  background: white;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.modal-header h2 {
  font-size: 20px;
  margin: 0;
}

.modal-close {
  position: absolute;
  top: 20px;
  right: 52px;
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
}

.modal-body h3 {
  margin-top: 20px;
  font-size: 16px;
  color: #333;
}

.modal-body p,
.modal-body ul {
  font-size: 14px;
  color: #555;
  margin: 5px 0;
}

.modal-body ul {
  padding-left: 20px;
}

.modal-body a {
  color: #0077cc;
  text-decoration: underline;
}
</style>
