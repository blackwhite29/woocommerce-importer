class WooCommerceImporter {
    constructor() {
        this.listen();
    }

    listen() {
        $(document).on('submit', '.import-woocommerce-form', this.import.bind(this));
    }

    import(event) {
        event.preventDefault();
        let $form = $(event.currentTarget);

        $('.woocommerce-importer .alert').addClass('hidden');

        const $button = $form.find('button[type=submit]');

        this
            .call({
                type: 'POST',
                url: $form.prop('action'),
                data: new FormData($form[0]),
                beforeSend: () => {
                    $button.addClass('button-loading');
                },
                complete: () => {
                    $button.removeClass('button-loading');
                }
            })
            .then(res => {
                if (!res.error) {
                    Botble.showSuccess(res.message);
                    this.importProduct(event, res.data);
                    $('.woocommerce-importer .success-message').removeClass('hidden').text(res.message);
                } else {
                    Botble.showError(res.message);
                    $('.woocommerce-importer .error-message').removeClass('hidden').text(res.message);
                }
            })
            .catch(error => {
                Botble.handleError(error);
            });
    }

    importProduct(event, data) {
        event.preventDefault();
        let $form = $(event.currentTarget);

        $('.woocommerce-importer .alert').addClass('hidden');

        const $button = $form.find('button[type=submit]');
        let formData = new FormData($form[0]);
        formData.set('current_page', data.current_page);
        formData.set('products', data.products);
        formData.set('has_next', data.has_next);
        this
            .call({
                type: 'POST',
                url: $form.prop('action'),
                data: formData,
                beforeSend: () => {
                    $button.addClass('button-loading');
                },
                complete: () => {
                    $button.removeClass('button-loading');
                }
            })
            .then(res => {
                if (!res.error) {
                    Botble.showSuccess(res.message);
                    if (res.data.has_next) {
                        this.importProduct(event, res.data);
                    }
                    $('.woocommerce-importer .success-message').removeClass('hidden').text(res.message);
                } else {
                    Botble.showError(res.message);
                    $('.woocommerce-importer .error-message').removeClass('hidden').text(res.message);
                }
            })
            .catch(error => {
                Botble.handleError(error);
            });

    }

    call(obj) {
        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'GET',
                contentType: false,
                processData: false,
                ...obj,
                success(res) {
                    resolve(res);
                },
                error(res) {
                    reject(res);
                },
            });
        })
    }
}

$(() => {
    new WooCommerceImporter();
});
