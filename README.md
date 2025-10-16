# Progetto-Da-Matta

Client realizzato con nuxt / vue in modalità single page per proteggere la parte admin.

Il server è stato sviluppato in php per semplicità e disponibilità gratuita (e aggiornata) su hosting gratuiti.

Come hosting è stato utilizzato [infinity](https://www.infinityfree.com/).

Lato database è stato utilizzato un database relazionale sql.

Riguardo alla parte dei pagamenti, non si è potuto utilizzare come conferma di pagamento il callback nativo di satispay perchè
richiedeva un callback_url con un certificato ssl valido che su InfinityFree non era disponibile.
Quindi ad ogni operazione che riguarda e necessita di:
    
    1) avere un numero aggiornato di iscritti guardando se il pagamento è andato a buon fine o no
    2) visualizzazione lato admin degli iscritti / pagamenti del giorno
    3) nella pagina event (e iscrizione) per quanto riguarda la logica di posti disponibili
è stata aggiunta una funzione validaPagamentiRegistrati($idEvento) che inserisce negli iscritti i pagamenti confermati
e elimina dalla tabella PendingPagamenti quelli non confermati / scaduti.

Per quanto riguarda la parte di chiavi / file protetti è tutto dentro la cartella /secure protetta da un suo ulteriore .htaccess
che elimina la possibilità di poterci accedere con la chiave del token e di satispay.

Per quanto riguarda lo storage dei file, tutti i file immagini caricati saranno convertiti in file webp con qualità abbassata
da 100% a 85% per risparmiare memoria. 
ATTENZIONE (se vengono caricati file .ico genererà un errore perchè le librerie di ImageIck e quelle di php non lo permettono)

Ci tengo a ricordare che il limite massimo di spazio è di 5gb per il pacchetto gratuito di infinityfree
