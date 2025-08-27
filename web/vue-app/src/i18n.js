import { createI18n } from 'vue-i18n';
import en from './locale/en.json';
import de from './locale/de.json';

const messages = {
    en,
    de
};

const i18n = createI18n({
    locale: 'en',
    fallbackLocale: 'en',
    messages,
    globalInjection: true,
    legacy: false,
});

export default i18n;