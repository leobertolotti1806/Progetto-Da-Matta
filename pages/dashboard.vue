<script setup>
useHead({ title: "Dashboard" });
import QRCode from "qrcode";

const sidebarOpen = ref(false);

const sezione = ref(null), headerTitle = ref(null), refreshSezione = ref(null);
const params = {
    Iscrizioni: {
        endpoint: "Eventi",
        titolo: "Scegli un'evento",
        name: "Iscrizione",
        showBtnSub: true
    },
    Eventi: {
        endpoint: "Eventi",
        name: "Evento",
        titolo: "Eventi"
    },
    Vini: {
        endpoint: "Vini",
        name: "Vino",
        titolo: "Vini"
    },
    Home: {
        titolo: "Home"
    },
    About: {
        titolo: "About"
    },
    Events: {
        titolo: "Eventi"
    },
    Contatti: {
        titolo: "Contatti"
    },
    Loghi: {
        titolo: "Loghi"
    },
    "Genera QrCode": {
        titolo: "Genera QrCode"
    },
    "Pagamenti del giorno":{
        titolo: "Pagamenti del giorno"
    }
}

function select(section) {
    sezione.value = "";
    sezione.value = section;
    refreshSezione.value = section + Math.random();
    sidebarOpen.value = false;
    headerTitle.value = (params[section].titolo == "About" ? "Visitaci" : params[section].titolo);
    setTimeout(() => {
        useHead({ title: "Dashboard - " + headerTitle.value });
    }, 100);
}

onMounted(() => {
    document.addEventListener('click', handleOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleOutsideClick);
});

const sidebarRef = ref(null);
const menuMobileRef = ref(null);

function handleOutsideClick(e) {
    if (menuMobileRef.value.contains(e.target)) {
        sidebarOpen.value = true;
    } else if (sidebarOpen.value && !sidebarRef.value.contains(e.target)) {
        sidebarOpen.value = false;
    }
}

const link = ref("");
const qrCodeUrl = ref("");

const generaQR = () => {
    if (!link.value) {
        qrCodeUrl.value = "";
        return;
    }
    QRCode.toDataURL(link.value)
        .then((url) => {
            qrCodeUrl.value = url;
        })
        .catch((err) => {
            console.error("Errore nella generazione del QR Code:", err);
        });
};

</script>
<template>
    <MyLoading />
    <div class="admin-layout">
        <aside :class="{ open: sidebarOpen }" ref="sidebarRef">
            <div class="logo">Pannello</div>
            <nav>
                <button @click="select('Eventi')">Eventi</button>
                <button @click="select('Vini')">Vini</button>
                <button @click="select('Iscrizioni')">Iscritti</button>
            </nav>
            <div class="logo">Layout</div>
            <nav>
                <button @click="select('Home')">Home</button>
                <button @click="select('About')">Visitaci</button>
                <button @click="select('Events')">Eventi</button>
            </nav>
            <div class="logo">Altro</div>
            <nav>
                <button @click="select('Pagamenti del giorno')">Pagamenti del giorno</button>
                <button @click="select('Genera QrCode')">Genera QrCode</button>
                <button @click="select('Contatti')">Contatti</button>
                <button @click="select('Loghi')">Loghi</button>
            </nav>
            <div class="logo">Ci sono problemi?</div>
            <nav>
                <button style="text-align: center; cursor: default;">366 391 3988</button>
            </nav>
        </aside>

        <div class="main">
            <header>
                <button class="menu-toggle" ref="menuMobileRef">☰</button>
                <h1>{{ headerTitle ?? "Admin" }}</h1>
            </header>
            <main>
                <h1 v-if="!sezione">
                    Scegli cosa visualizzare nei tab a lato
                    <v-icon class="icon" style="margin-left: 8px;">mdi-arrow-left</v-icon>
                </h1>

                <MyAdminModal v-else-if="sezione == 'Contatti' || sezione == 'Loghi'" @close="sezione = null"
                    :name="sezione" mode="Modifica" />

                <myAdminEdit v-else-if="sezione == 'Home' || sezione == 'About' || sezione == 'Events'" :page="sezione"
                    :key="refreshSezione" />

                <div v-else-if="sezione == 'Genera QrCode'" class="qrCodeDiv">
                    <h2>Genera il tuo QR Code</h2>

                    <MyInput v-model="link" type="text" placeholder="Inserisci un link o un testo..." />

                    <button @click="generaQR" class="btn">Genera</button>

                    <div v-if="qrCodeUrl" class="qr-preview">
                        <img :src="qrCodeUrl" alt="QR Code generato" />
                        <a :href="qrCodeUrl" download="qrcode.png" class="btn">
                            Scarica
                        </a>
                    </div>
                </div>

                <myPagamenti v-else-if="sezione == 'Pagamenti del giorno'" :key="refreshSezione"/>

                <myAdminTable v-else :data="params[sezione]" :key="refreshSezione" @update-titolo="headerTitle = $event" />
            </main>
        </div>
    </div>
    <a href="/home" id="backHome"><v-icon class="icon">mdi-home</v-icon></a>
</template>
<style scoped>
.qrCodeDiv a.btn{
    background-color: #3d7a09;
}
.qrCodeDiv , .qr-preview{
    width: 100%;
    display: flex;
    align-items: center;
    flex-direction: column;
    row-gap: 2vh;
}
.qrCodeDiv img{
    padding: 7px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 10px;
}
.qr-preview{
    justify-content: center;
    margin-top: 5vh;
}
.admin-layout {
    display: flex;
    min-height: 100vh;
}

main {
    display: flex;
    justify-content: center;
    height: 100%;
    padding: 24px;
}

main>h1 {
    padding: 5vw;
    text-align: center;
}

aside {
    min-width: 180px;
    width: 200px;
    background: #2c3e50;
    border-right: 2px #cdd0d4 solid;
    color: white;
    display: flex;
    flex-direction: column;
    gap: 20px;
    transition: transform 0.3s;
    overflow-y: auto;
}

h1 {
    font-size: 50px;
}

aside .logo {
    font-weight: bold;
    font-size: 18px;
    text-align: center;
}

aside .logo:first-of-type {
    margin-top: 10px;
}

nav {
    display: flex;
    flex-direction: column;
}

aside nav button:first-child{
    padding-top: 0;
}

aside nav button {
    text-align: left;
    border-bottom: rgba(245, 245, 245, 0.8) 2px solid;
    padding: 15px 10px;
    color: white;
    width: 100%;
    font-size: 16px;
}

.main {
    flex: 1;
    display: flex;
    flex-direction: column;
}

header {
    background: #f5f5f5;
    padding: 16px;
    box-sizing: border-box;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
}

.menu-toggle {
    display: none;
    background: none;
    font-size: 24px;
    border: none;
    cursor: pointer;
}

@media (max-width: 1024px) {
    aside {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        width: 200px;
        transform: translateX(-100%);
        z-index: 100;
    }

    main {
        padding: 2vh 0;
    }

    aside.open {
        transform: translateX(0);
    }

    .menu-toggle {
        display: block;
    }
}

@media (max-width: 768px) {

    aside nav button {
        font-size: 13px;
        padding: 10px 6px;
    }

    aside {
        gap: 16px;
    }

    h1 {
        font-size: 35px;
    }

    header h1 {
        margin-left: 16px;
    }
}
</style>