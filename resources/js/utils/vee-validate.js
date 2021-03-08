import { confirmed, email, max, min, required } from 'vee-validate/dist/rules';
import { extend, setInteractionMode } from 'vee-validate';

extend('required', {
    ...required,
    message: '{_field_} can not be empty',
});

extend('max', {
    ...max,
    message: '{_field_} may not be greater than {length} characters',
});

extend('email', {
    ...email,
    message: 'Email must be valid',
});

extend('min', {
    ...min,
    message: 'The {_field_} field must have at least {length} characters',
});

extend('password_confirmed', {
    ...confirmed,
    message: 'Password confirmation does not match.',
});

extend('agree_tos', {
    ...required,
    message: 'You must agree to continue.',
});

extend('url', {
    validate: (str) => {
        let url;

        try {
            url = new URL(str);
        } catch (_) {
            return false;
        }

        return url.protocol === 'http:' || url.protocol === 'https:';
    },
    message: 'The {_field_} field is not a valid URL.',
});

setInteractionMode('eager');
