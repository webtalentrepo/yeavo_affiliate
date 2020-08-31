import { required, email, max, min, confirmed } from 'vee-validate/dist/rules';
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

setInteractionMode('eager');
