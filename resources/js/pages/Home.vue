<template>
    <div>
        <h3>Добавить новое слово</h3>
        <form @submit.prevent="save">
            <div class="input-group">
                <label for="source_lang">Оригинал</label>
                <input
                    type="text"
                    v-model="sourceLang"
                    id="source_lang"
                >
            </div>
            <div class="input-group">
                <label for="target_lang">Перевод</label>
                <input
                    type="text"
                    v-model="targetLang"
                    id="target_lang"
                >
            </div>
            <p class="message-result" v-if="isResponseMessage">{{ responseMessage }}</p>
            <button :disabled="!isReadyStoreWord">Сохранить</button>
        </form>
        <h3>Тренировка</h3>
        <p>Даты:</p>
        <ul>
            <li v-for="day in daysList">
                <a
                    href="#"
                    :class="{active: selectDay && selectDay === day.id}"
                    @click.prevent="selectDayFunc(day.id)"
                >
                    {{ day.date }}
                </a>
            </li>
        </ul>
        <button
            v-if="!isStart && selectDay"
            class="start-button"
            @click.prevent="start"
        >Начать
        </button>
        <div class="check" v-if="isStart">
            <p class="source-word">
                Что значит: <b>{{ word.source }}</b>
                <span class="target-word" v-if="isTarget">= {{ word.target }}</span>
            </p>
            <input type="text" v-model="answer" @keyup.enter="check">
            <button @click.prevent="isTarget = true">Ответ</button>
            <button @click.prevent="nextWord">Следующее</button>
            <p class="message-result correct" v-if="isEnterPress" :class="{
                correct: isCorrect,
                incorrect: !isCorrect
            }">{{ isCorrect ? 'верно' : 'ошибка' }}!</p>
            <p style="font-size: 14px;" v-if="isCorrect">нажмите еще раз enter чтобы получит следующее слово</p>
        </div>
    </div>
</template>

<script>
export default {
    name: "Home",
    data: () => ({
        sourceLang: '',
        targetLang: '',
        daysList: false,
        selectDay: false,
        isStart: false,
        isTarget: false,
        isCorrect: false,
        isEnterPress: false,
        word: {
            id: false,
            source: false,
            target: false
        },
        answer: '',
        isResponseMessage: false,
        responseMessage: ''
    }),
    computed: {
        isReadyStoreWord() {
            return !!(!/^\s*$/.test(this.sourceLang) && !/^\s*$/.test(this.targetLang));
        }
    },
    mounted() {
        this.days();
    },
    methods: {
        save() {

            if (!this.isReadyStoreWord) {
                return;
            }

            this.isResponseMessage = false;
            this.responseMessage = '';

            this.axios.post(`/api/v1/word`, {
                source: this.sourceLang,
                target: this.targetLang
            }).then(res => {
                if (res.data.status) {
                    this.isResponseMessage = true;
                    this.responseMessage = 'Добавлено';
                    this.sourceLang = '';
                    this.targetLang = '';
                }
            }).catch(res => {
                this.isResponseMessage = true;
                this.responseMessage = 'Ошибка при добавлени нового слова'
            })
        },
        check() {
            this.isEnterPress = true;

            if (this.isCorrect === true) {
                this.nextWord();
            } else {
                if (this.answer === this.word.target) {
                    this.isCorrect = true;
                }
            }
        },
        days() {
            this.axios.get('/api/v1/days').then(res => {
                this.daysList = res.data.days;
            })
        },
        getWord() {
            this.axios.get(`/api/v1/word?type=random&day=${this.selectDay}`)
                .then(res => {
                    this.word.id = res.data.word.id;
                    this.word.source = res.data.word.source;
                    this.word.target = res.data.word.target;
                })
        },
        nextWord() {
            this.answer = '';
            this.isTarget = false;
            this.isCorrect = false;
            this.isEnterPress = false;
            this.getWord();
        },
        selectDayFunc(id) {
            this.selectDay = id;
        },
        start() {
            this.isStart = true;
            this.getWord();
        }
    }
}
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    font-size: 24px;
    font-family: "JetBrainsMono Nerd Font Mono";
}

li {
    display: inline-block;
    margin-right: 20px;
}

form {
    margin: 20px 0;
}

a.active {
    font-weight: bold;
    color: red;
}

.check {
    margin-top: 40px;
}

.message-result {
    margin-top: 10px;
    font-weight: bold;
}

.message-result.correct {
    color: green;
}

.message-result.incorrect {
    color: red;
}

.start-button {
    margin-top: 20px;
}
</style>
