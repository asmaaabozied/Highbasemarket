import pluginVue from 'eslint-plugin-vue'
import eslintPluginTranslation from "./eslint-plugin-translation/index.js";


export default [
    // add more generic rulesets here, such as:
    // js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    // ...pluginVue.configs['flat/vue2-recommended'], // Use this if you are using Vue.js 2.x.
    {
        plugins: {
            'translation': eslintPluginTranslation,
        },
        files: ['**/*.js', '**/*.vue'],
        rules: {
            indent: ['error', 4],
            'vue/html-indent': ['error', 4],
            'vue/multi-word-component-names': 0,
            'vue/require-default-prop': 0,
            'no-console': 'error',
            'padding-line-between-statements': [
                'error',

                // blank line before block-like statements (if, while, for, etc.)
                { blankLine: 'always', prev: '*', next: 'block-like' },
                // blank line after block-like statements
                { blankLine: 'always', prev: 'block-like', next: '*' },

                // blank line before function declarations
                { blankLine: 'always', prev: '*', next: 'function' },
                // blank line after function declarations
                { blankLine: 'always', prev: 'function', next: '*' },
            ],
            'translation/no-plain-text': 'error',
            'translation/no-untranslated-placeholders': 'error',
        }
    }
]
