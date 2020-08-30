import { required, email, max } from 'vee-validate/dist/rules';
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

setInteractionMode('eager');
