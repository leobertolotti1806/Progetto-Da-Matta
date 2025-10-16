<script setup>
import { getData, getIdKeyAsArrayKey } from '~/assets/script';
const paragraphs = ref([]);
getData("/getParagraphs?page=footer").then(function (esito) {
    if (!esito.ok) {
        if (!esito.msg) {
            alert("Ricarica la pagina");
            return;
        }
        alert(esito.msg);
    } else {
        paragraphs.value = getIdKeyAsArrayKey(esito.paragrafi);
    }
});
</script>
<template>
    <footer>
        <div class="footer-brand">
            <h2>{{ paragraphs[33] }}</h2>
        </div>

        <div class="footer-contact">
            <p>📍 <a :href="`https://www.google.it/maps/place/${paragraphs[38]}`">{{ paragraphs[38] }}</a></p>
            <p>📞 <a :href="`tel:${paragraphs[34]}`">{{ paragraphs[34] }}</a></p>
            <p>✉️ <a :href="`mailto:${paragraphs[35]}`">{{ paragraphs[35] }}</a></p>
        </div>

        <div class="footer-social">
            <a :href="paragraphs[36]" target="_blank" class="social-btn">
                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/instagram.svg" alt="Instagram" />
            </a>
            <a :href="paragraphs[37]" target="_blank" class="social-btn">
                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/facebook.svg" alt="Facebook" />
            </a>
        </div>
    </footer>
</template>
<style scoped>
footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    height: 15vh;
    padding: 2vh 1vh;
    color: #fff;
    text-align: center;
    gap: 1.5rem;
    background-color: #222;
    font-family: sans-serif;
}

footer>div {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

footer a {
    color: #ddd;
    text-decoration: 1px underline #ddd;
}

footer a:hover {
    color: #fff;
}

footer h2 {
    margin: 0;
    font-size: 22px;
    width: 100%;
    color: #fff;
}

.footer-brand {
    width: 33.33%;
}

.footer-contact {
    flex-direction: column;
    width: 66.66%;
}

.footer-contact p {
    margin: 0.3rem 0;
    font-size: 0.95rem;
    width: 80%;
}

.footer-social {
    gap: 1rem;
    width: 33.33%;
}

.social-btn img {
    width: 32px;
    height: 32px;
    filter: invert(100%);
    transition: transform 0.2s ease;
}

.social-btn:hover img {
    transform: scale(1.1);
}


@media screen and (max-width: 768px) {
    footer {
        justify-content: space-between;
        height: 18vh;
    }

    footer h2 {
        font-size: 18px;
    }

    .footer-contact {
        width: 75%;
    }

    .footer-brand,
    .footer-social {
        width: 12.5%;
    }
}

@media screen and (max-width: 480px) {
    footer {
        flex-direction: column;
        min-height: 37vh;
    }

    footer h2 {
        font-size: 18px;
    }

    footer>div {
        width: 100% !important;
        height: 6vh;
    }

    .social-btn img {
        width: 28px;
        height: 28px;
    }
}
</style>