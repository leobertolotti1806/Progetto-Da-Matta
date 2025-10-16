import axios from "axios";

export function longStringDate(data) {
  return new Date(data).toLocaleDateString('it-IT', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
}

export function toSelectOptions(array) {
  if (
    Array.isArray(array) && array.length > 0 &&
    typeof array[0] === 'object' &&
    array[0] !== null && 'Id' in array[0]) {
    // Prende il primo attributo diverso da Id come testo
    const textKey = Object.keys(array[0]).find(k => k !== 'Id');

    return array.map(item => ({
      text: item[textKey],
      value: item.Id
    }));
  }

  // Caso normale: array di stringhe o valori primitivi
  return array.map(item => ({
    text: item,
    value: item
  }));
}

export function getIdKeyAsArrayKey(array) {
  const newArr = [];
  array.forEach(el => {
    newArr[el.Id] = el.Testo;
  });
  return newArr;
}

export function camelToSpaced(str) {
  return str.replace(/([a-z])([A-Z])/g, '$1 $2');
}

export function toSlashDate(dataStr) {
  const [yyyy, mm, dd] = dataStr.split("-");
  return `${dd}/${mm}/${yyyy}`;
}

// GET
export async function getData(url, params = {}) {
  try {
    return (await axios.get(url, {
      params,
      headers: { 'Content-Type': 'application/json' },
      withCredentials: true
    })).data;
  } catch (err) {
    return handleError(err);
  }
}

// POST
export async function postData(url, payload = {}) {
  try {
    return (await axios.post(url, payload, { 'Content-Type': 'application/json', withCredentials: true })).data;
  } catch (err) {
    return handleError(err);
  }
}

export async function postFormData(url, payload = {}, fileOrFiles = null) {
  let form = new FormData();

  for (let key in payload) {
    form.append(key, payload[key])
  }

  if (fileOrFiles) {

    if (Array.isArray(fileOrFiles) || Object.is(fileOrFiles)) {

      for (let key in fileOrFiles) {
        if (fileOrFiles[key] instanceof File) {
          form.append(key, fileOrFiles[key]);
        }
      }
    } else {
      // Caso singolo file
      form.append("file", fileOrFiles);
    }
  }

  try {
    return (await axios.post(url, form, { withCredentials: true })).data;
  } catch (err) {
    return handleError(err);
  }
}

function handleError(err) {
  let msg;

  if (err.response?.data?.msg) {
    msg = err.response.data.msg;
    console.error("Errore dal server:", msg);
  } else if (err.response) {
    msg = "Errore dal server!";
    console.error("Errore dal server (no msg): ", err.response.data);
  } else if (err.request) {
    msg = "Nessuna risposta dal server!";
    console.error("Nessuna risposta dal server: ", err.request);
  } else {
    msg = "Errore imprevisto!";
    console.error("Errore di configurazione: " + err.message);
  }

  return { msg };
}