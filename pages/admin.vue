<script setup>
useHead({ title: "Admin" });
import { postData } from '~/assets/script';

const step = ref(0);
const form = reactive({ email: "", password: "" });

async function handleLogin() {
    document.body.classList.add("cursorWait");
    let esito = await postData("/adminLogin", { email: form.email, pwd: form.password });
    document.body.classList.remove("cursorWait");

    if (!esito.ok) {
        alert(esito.msg);
    } else {
        window.location.href = "/dashboard";
    }
}
</script>

<template>
    <MyLoading/>
    <div class="admin-container">
        <div v-if="step === 0" class="login-form">
            <h2>Accesso Amministratore</h2>
            <form @submit.prevent="handleLogin">
                <MyInput label="Email" placeholder="Inserisci la tua email" type="email" required
                    v-model="form.email" />
                <MyInput label="Password" placeholder="Password" type="password" required v-model="form.password" />
                <button type="submit">Accedi</button>
            </form>
        </div>
    </div>
    <a href="/home" id="backHome"><v-icon class="icon">mdi-home</v-icon></a>
</template>
<style scoped>
.admin-container {
    max-width: 400px;
    margin: 100px auto;
    padding: 24px;
    background: #fdfdfd;
    border: 1px solid #ddd;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #2c3e50;
}

form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

button{
    margin-top: 10px;
    padding: 12px;
    background-color: #a1864f;
    color: white;
    border: none;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: background, transform 0.3s;
}

button:hover {
    transform: scale(1.02);
    background-color: #8c7540;
}

@media (max-width: 600px) {
    .admin-container {
        margin: 60px 16px;
        padding: 16px;
    }
}
</style>