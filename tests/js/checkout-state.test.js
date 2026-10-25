import assert from 'node:assert/strict';
import test from 'node:test';
import { syncCheckoutSubmitButtons } from '../../public/js/checkout-state.js';

function makeDocument() {
    const buttons = [{ disabled: true, name: 'desktop' }, { disabled: true, name: 'mobile' }];
    let selector = '';

    return {
        buttons,
        get selector() { return selector; },
        querySelectorAll(value) {
            selector = value;
            return buttons;
        },
    };
}

const validState = () => ({
    addressReady: true,
    courierReady: true,
    quoteToken: 'signed-server-quote',
    cost: 15000,
    quoteExpiresAt: Math.floor(Date.now() / 1000) + 60,
    busy: false,
});

test('a valid quote enables both desktop and mobile checkout buttons', () => {
    const document = makeDocument();

    assert.equal(syncCheckoutSubmitButtons(document, validState()), true);
    assert.equal(document.selector, '#checkout-form button[type="submit"], button[form="checkout-form"]');
    assert.deepEqual(document.buttons.map((button) => button.disabled), [false, false]);
});

test('an invalid, expired, or busy quote keeps both checkout buttons disabled', (context) => {
    const cases = [
        ['missing quote token', (state) => { state.quoteToken = ''; }],
        ['zero tariff', (state) => { state.cost = 0; }],
        ['expired quote', (state) => { state.quoteExpiresAt = Math.floor(Date.now() / 1000) - 1; }],
        ['missing address', (state) => { state.addressReady = false; }],
        ['missing courier', (state) => { state.courierReady = false; }],
        ['request in progress', (state) => { state.busy = true; }],
    ];

    for (const [name, invalidate] of cases) {
        context.test(name, () => {
            const document = makeDocument();
            const state = validState();
            invalidate(state);

            assert.equal(syncCheckoutSubmitButtons(document, state), false);
            assert.deepEqual(document.buttons.map((button) => button.disabled), [true, true]);
        });
    }
});
