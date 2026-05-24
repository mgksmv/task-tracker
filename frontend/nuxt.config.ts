import globalPt from './app/theme/global-pt';
import customThemePreset from './app/theme/noir-preset';
import tailwindcss from '@tailwindcss/vite';

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  ssr: false,
  modules: ['@primevue/nuxt-module', '@pinia/nuxt', 'pinia-plugin-persistedstate/nuxt', '@nuxt/eslint', 'dayjs-nuxt'],
  runtimeConfig: {
    public: {
      appName: process.env.APP_NAME,
      appDomain: process.env.APP_DOMAIN,
      appURL: process.env.APP_URL,
      backendDomain: process.env.BACKEND_DOMAIN,
      backendURL: process.env.BACKEND_URL,
    },
  },
  css: ['./app/assets/css/app.css'],

  vite: {
    plugins: [tailwindcss()],
  },

  dayjs: {
    locales: ['ru'],
    plugins: ['utc', 'timezone', 'relativeTime', 'customParseFormat'],
    defaultLocale: 'ru',
    defaultTimezone: 'Europe/Moscow',
  },

  primevue: {
    options: {
      theme: {
        preset: customThemePreset,
        options: {
          darkModeSelector: '.dark',
        },
      },
      pt: globalPt,
      locale: {
        dayNames: [
          'Воскресенье',
          'Понедельник',
          'Вторник',
          'Среда',
          'Четверг',
          'Пятница',
          'Суббота',
        ],
        dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        monthNames: [
          'Январь',
          'Февраль',
          'Март',
          'Апрель',
          'Май',
          'Июнь',
          'Июль',
          'Август',
          'Сентябрь',
          'Октябрь',
          'Ноябрь',
          'Декабрь',
        ],
        monthNamesShort: [
          'Янв',
          'Фев',
          'Мар',
          'Апр',
          'Май',
          'Июн',
          'Июл',
          'Авг',
          'Сен',
          'Окт',
          'Ноя',
          'Дек',
        ],
        today: 'Сегодня',
        weekHeader: 'Нед',
        firstDayOfWeek: 1,
        dateFormat: 'dd.mm.yy',
        accept: 'Да',
        reject: 'Нет',
        apply: 'Применить',
        choose: 'Выбрать',
        noFileChosenMessage: 'Файл не выбран',
      },
    },
  },
})
