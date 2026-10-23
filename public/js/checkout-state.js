export function syncCheckoutSubmitButtons(document, state) {
    const quoteIsValid = Boolean(state.quoteToken)
        && Number.isInteger(state.cost)
        && state.cost > 0
        && Number.isInteger(state.quoteExpiresAt)
        && state.quoteExpiresAt * 1000 > Date.now();
    const canSubmit = Boolean(state.addressReady && state.courierReady && quoteIsValid && !state.busy);

    document
        .querySelectorAll('#checkout-form button[type="submit"], button[form="checkout-form"]')
        .forEach((button) => {
            button.disabled = !canSubmit;
        });

    return canSubmit;
}
