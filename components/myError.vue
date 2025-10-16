<script setup>
const props = defineProps({
  obj: Object
});

const title = ref(!props.obj.title  ? "Errore" : props.obj.title);
const msg = ref(!props.obj.msg ? "Ci dispiace ma non sono stati passati i parametri giusti per visualizzare questa pagina" : props.obj.msg);
const link = ref(!props.obj.link ? "/home" : props.obj.link);
const linkMsg = ref(!props.obj.linkMsg ? "Torna alla home" : props.obj.linkMsg);

</script>
<template>
  <div class="error-container">
    <div class="error-box">
      <h1>{{ title }}</h1>
      <p>{{ msg }}</p>
      <a :href="link" class="back-home">{{ linkMsg }}</a>
    </div>
  </div>
</template>

<style scoped>
.error-container {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #fafafa;
  text-align: center;
  padding: 20px;
}

.error-box {
  max-width: 500px;
  padding: 40px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  animation: fadeIn 0.4s ease-out;
}

.error-box h1 {
  font-size: 80px;
  margin: 0;
  color: #b00020;
}

.error-box p {
  font-size: 18px;
  color: #444;
  margin: 16px 0 24px;
}

.back-home {
  display: inline-block;
  padding: 10px 20px;
  background: #800000;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  transition: background 0.3s;
}

.back-home:hover {
  background: #a00000;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
