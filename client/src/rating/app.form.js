import { Form } from 'mobx-react-form';
import validatorjs from 'validatorjs';
import dvr from 'mobx-react-form/lib/validators/DVR';

export default class AppForm extends Form {
    /**
     * @type AppStore
     */
    store = null;

    /**
     * Set the store for the form
     *
     * @param AppStore store
     */
    setStore(store) {
        this.store = store;
    }

    /**
     * Return a plugins object using the `validatorjs` package
     * to enable Declarative Validation Rules
     */
    plugins() {
        return {
            dvr: dvr({
                package: validatorjs,
                extend: ({ validator, form }) => {
                    const messages = validator.getMessages('en');
                    messages.between = 'Comments must be between :min and :max characters';
                    validator.setMessages('en', messages);
                }
            })
        };
    }

    /**
     * Options for the form, including validation options
     */
    options() {
        return {
            validateOnInit: false,
            validateOnChange: true,
            showErrors: true
        };
    }

    /**
     * Form field items and validation rules
     */
    setup() {
        return {
            fields: [
                {
                    name: 'rating',
                    type: 'radio',
                    rules: 'required|integer|min:1'
                },
                {
                    name: 'comments',
                    rules: 'string|between:1,500'
                },
                {
                    name: 'tags',
                    rules: 'string'
                },
                {
                    name: 'pageName',
                    type: 'hidden'
                },
                {
                    name: 'pageID',
                    type: 'hidden'
                }
            ]
        };
    }

    /**
     * Event Hooks
     */
    hooks() {
        return {
            onSubmit(form) {
            },
            onSuccess(form) {
                if (!this.store) {
                    return;
                }

                this.store.rate(form.values());
            },
            onError(form) {
                form.invalidate();
            }
        };
    }
}
