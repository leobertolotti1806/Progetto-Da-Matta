<script setup>
const props = defineProps({
    label: { type: String, default: null },
    type: { type: String, default: 'text' },
    placeholder: String,
    required: { type: Boolean, default: null },
    minlength: { type: Number, default: null },
    maxlength: { type: Number, default: null },
    pattern: { type: String, default: null },
    modelValue: { default: null },
    defaultValue: { default: null },
    data: { type: Array, default: () => [] }// solo per select
});

const emit = defineEmits(['update:modelValue', 'changedImage', 'invioPremuto']);
const showPassword = ref(false);

const imagePreview = ref(null);
const inputFile = ref(null);

const hasInvioListener = 'onInvioPremuto' in getCurrentInstance().vnode.props;


onMounted(() => {
    if (props.type === 'select' && props.defaultValue) {

        const found = props.data.find(function (opt) {
            if (parseInt(props.defaultValue)) {
                return opt.value == props.defaultValue;
            } else {
                return opt.text.toLowerCase() == props.defaultValue.toLowerCase();
            }
        });

        if (found)
            emit('update:modelValue', found.value)
    } else if (props.type === 'image') {
        if (props.modelValue) {
            imagePreview.value = props.modelValue;
        } else if (props.defaultValue) {
            imagePreview.value = `/img${props.defaultValue}.webp?d=${Math.random()}`;
        } else {
            imagePreview.value = "";
        }
    } else if (props.type === 'tel' && props.defaultValue) {
        prefix.value = props.defaultValue.substr(0, 3);
        phoneNumber.value = props.defaultValue.substr(3);
        emit('update:modelValue', props.defaultValue);
    } else if (props.defaultValue) {
        emit('update:modelValue', props.defaultValue)
    }
});

const prefixes = ['+39', '+01', '+33', '+49', '+34', '+41', '+31', '+32', '+48', '+30', '+47', '+48']; // aggiungi altri se vuoi
const prefix = ref(prefixes[0]);
const phoneNumber = ref('');

let firstTime = true;

watch(() => props.modelValue, (val) => {

    if (props.type === 'tel' && val) {
        // Valore effettivo da parsare (es. "+393331112233")
        if (firstTime && props.defaultValue) {
            firstTime = false;
            prefix.value = props.defaultValue.substr(0, 3);
            phoneNumber.value = props.defaultValue.substr(3);
        } else {
            const currentValue = val || props.defaultValue;
    
            // Se vuoto, esce
            if (!currentValue) return;
    
            // Se è già composto da prefix e phoneNumber correnti, non fare nulla
            const currentFull = prefix.value + phoneNumber.value;
            if (currentValue === currentFull) return;
    
            // Prova a splittare il valore
            const match = currentValue.match(/^(\+\d{2,3})(\d{6,})$/);
            if (match) {
                prefix.value = match[1];
                phoneNumber.value = match[2];
            }
        }
    } else if (props.type === 'image') {
        if (val && typeof val === 'string') {
            imagePreview.value = `/img${val}.webp?d=${Math.random()}`;
        }
    }
}, { immediate: true });

watch([prefix, phoneNumber], () => {
    if (props.type === 'tel') {
        const combined = prefix.value + phoneNumber.value;
        emit('update:modelValue', combined);
    }
});

function handleFileChange(e) {
    const file = e.target.files[0];

    if (file) {
        emit('update:modelValue', file); // passiamo il file fisico
        emit('changedImage');
        imagePreview.value = file;
        const reader = new FileReader();
        reader.onload = () => {
            imagePreview.value = reader.result;
        };
        reader.readAsDataURL(file);
    }
}

function handleDrop(e) {
    const file = e.dataTransfer.files[0];
    me
    if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = () => {
            imagePreview.value = reader.result;
        };
        reader.readAsDataURL(file);

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        inputFile.value.files = dataTransfer.files;

        emit("update:modelValue", file);
        emit('changedImage');
    }
}

</script>

<template>
    <div class="input-group">
        <label v-if="label">{{ label }}</label>

        <select v-if="type === 'select'" :required="required" :value="modelValue ?? defaultValue"
            @change="e => emit('update:modelValue', e.target.value)">
            <option value="" disabled selected hidden>{{ placeholder || defaultValue || 'Seleziona un valore' }}
            </option>
            <option v-for="option in data" :key="option.value" :value="option.value">
                {{ option.text }}
            </option>
        </select>

        <div v-else-if="type === 'password'" class="password-wrapper">
            <input :type="showPassword ? 'text' : 'password'" :placeholder="placeholder" :required="required"
                :minlength="minlength" :maxlength="maxlength"
                :pattern="pattern || '^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[\\W_]).{8,}$'"
                :value="modelValue ?? defaultValue" @input="$emit('update:modelValue', $event.target.value)" />
            <span class="toggle-password" @click="showPassword = !showPassword">
                {{ showPassword ? '🙈' : '👁️' }}
            </span>
        </div>

        <!-- Immagine -->
        <div v-else-if="type === 'image'" class="image-input">
            <div class="image-drop-area" @dragover.prevent @drop.prevent="handleDrop" @click="inputFile.click()">
                <img v-if="imagePreview" :src="imagePreview" alt="Anteprima immagine" class="preview-img" />
                <div v-else class="image-placeholder">
                    <span class="icon">🖼️</span>
                    <p>Trascina qui la tua immagine o clicca sotto</p>
                </div>
            </div>
            <button v-if="defaultValue" type="button" @click="inputFile.click()">Scegli un'altra immagine</button>
            <input type="file" accept="image/*" @change="handleFileChange" :required="required" ref="inputFile"
                :class="defaultValue ? 'show' : ''" />
        </div>

        <textarea v-else-if="type === 'textarea'" :placeholder="placeholder" :required="required" :minlength="minlength"
            :maxlength="maxlength" :value="modelValue ?? defaultValue"
            @input="$emit('update:modelValue', $event.target.value)">
        </textarea>

        <!-- Speciale input telefono -->
        <div v-else-if="type === 'tel'" class="tel-wrapper">
            <select v-model="prefix" class="prefix-select">
                <option v-for="p in prefixes" :key="p" :value="p">{{ p }}</option>
            </select>
            <input class="phone-input" type="tel" :placeholder="placeholder || 'Telefono'" :required="required"
                pattern="^\d{10}$" v-model="phoneNumber" maxlength="10" inputmode="numeric" />
        </div>
        <!-- Solo interi -->
        <input v-else-if="type === 'number'" type="number" :placeholder="placeholder" :required="required"
            :min="minlength ?? 0" :max="maxlength" :value="modelValue ?? defaultValue" inputmode="numeric"
            @input="e => emit('update:modelValue', e.target.value)" />

        <!-- Prezzo con due decimali -->
        <input v-else-if="type === 'price'" type="number" step="0.01" inputmode="decimal"
            :placeholder="placeholder || '0.00'" :required="required" :value="modelValue ?? defaultValue"
            @input="e => emit('update:modelValue', String(e.target.value).replace(',', '.'))" />

        <!-- Checkbox -->
        <div v-else-if="type === 'checkbox'" class="checkbox-wrapper">
            <label class="custom-checkbox">
                <input type="checkbox" :checked="modelValue ?? defaultValue"
                    @change="e => emit('update:modelValue', e.target.checked)" :required="required" />
                <span class="checkmark"></span>
                <span class="checkbox-label">{{ placeholder || label }}</span>
            </label>
        </div>

        <!-- Altri tipi -->
        <input v-else :type="type" :placeholder="placeholder" :required="required" :minlength="minlength"
            :maxlength="maxlength" :pattern="pattern" :value="modelValue ?? defaultValue"
            @input="$emit('update:modelValue', $event.target.value)" 
            v-on="hasInvioListener ? { keyup: e => e.key === 'Enter' && emit('invioPremuto') } : {}"/>
    </div>
</template>

<style scoped>
.input-group {
    display: flex;
    flex-direction: column;
    width: 100%;
}

label {
    font-size: 14px;
    margin-bottom: 6px;
    color: #333;
}

input,
select {
    padding: 10px 14px;
    font-size: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline-color: #800000;
    transition: border 0.2s, box-shadow 0.2s;
    background-color: white;
    appearance: none;
    background-image: none;
}

input[type=file] {
    width: 100%;
}

.show {
    max-width: 0px !important;
    max-height: 0px !important;
    border: none;
    padding: 0;
}

select {
    background-image: url("data:image/svg+xml,%3Csvg fill='gray' height='16' viewBox='0 0 24 24' width='16' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
    cursor: pointer;
}

input:focus,
select:focus {
    border-color: #800000;
    box-shadow: 0 0 5px rgba(128, 0, 0, 0.2);
}

textarea {
    padding: 10px 14px;
    font-size: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    resize: vertical;
    min-height: 120px;
    background-color: white;
    transition: border 0.2s, box-shadow 0.2s;
    outline-color: #800000;
    font-family: inherit;
}

textarea:focus {
    border-color: #800000;
    box-shadow: 0 0 5px rgba(128, 0, 0, 0.2);
}

.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper input {
    width: 100%;
    padding-right: 40px;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #666;
    user-select: none;
}

.image-input {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    width: 100%;
}

.image-drop-area {
    width: 100%;
    min-height: 200px;
    border: 2px dashed #ccc;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    background-color: #fafafa;
    transition: background 0.3s;
}

.image-drop-area:hover {
    background-color: #f0f0f0;
}

.image-placeholder {
    text-align: center;
    color: #888;
    font-size: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.image-placeholder .icon {
    font-size: 40px;
    margin-bottom: 8px;
}

.preview-img {
    max-width: 280px;
    width: 100%;
    max-height: 250px;
    object-fit: contain;
    border-radius: 8px;
}

.tel-wrapper {
    display: flex;
    gap: 10px;
}

.prefix-select {
    width: 90px;
    padding: 10px;
    font-size: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: white;
    outline-color: #800000;
}

.phone-input {
    flex: 1;
    padding: 10px 14px;
    font-size: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline-color: #800000;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    width: 100%;
}

.custom-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    user-select: none;
    font-size: 15px;
    color: #333;
    position: relative;
}

.custom-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    height: 18px;
    width: 18px;
    background-color: #fff;
    border: 2px solid #800000;
    border-radius: 4px;
    display: inline-block;
    position: relative;
    transition: background-color 0.2s ease;
}

.custom-checkbox input:checked~.checkmark {
    background-color: #800000;
}

.checkmark::after {
    content: "";
    position: absolute;
    display: none;
}

.custom-checkbox input:checked~.checkmark::after {
    display: block;
}

.custom-checkbox .checkmark::after {
    left: 5px;
    top: 1px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.checkbox-label {
    flex: 1;
}

.image-input button {
    padding: 10px 16px;
    font-size: 14px;
    background-color: #800000;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.image-input button:hover {
    background-color: #a00000;
}

.image-input button:focus {
    outline: 2px solid #ccc;
    outline-offset: 2px;
}

@media (max-width: 768px) {
    .preview-img {
        max-width: 100%;
        max-height: 160px;
    }

    .image-placeholder .icon {
        font-size: 30px;
    }
}

@media (max-width:480px) {

    input,
    select,
    label {
        font-size: 13px !important;
    }

    select {
        background-position: right 2px center;
    }
}
</style>