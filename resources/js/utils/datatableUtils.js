import Swal from 'sweetalert2';
import {useI18n} from 'vue-i18n';
import {handleAxiosError} from "@/utils/errorHandler.js";

export const createDataTableConfig = (options = {}) => {
    const {t} = useI18n();

    // Kolon tanımlamalarını düzenle
    const formattedColumns = options.columns.value.map(column => {
        // 'render' fonksiyonunu string'den gerçek fonksiyona çevir
        if (column.render && typeof column.render === 'string') {
            column.render = new Function('return ' + column.render)();
        }
        return column;
    });

    return {
        serverSide: true,
        processing: true,
        ajax: {
            url: options.dataUrl,
            error: function (xhr, error, thrown) {
                console.error('DataTables error:', error);
            }
        },
        columns: formattedColumns || [],
        drawCallback: function (settings) {
            const api = new $.fn.dataTable.Api(settings);


            if (options.onEdit) {
                $('.edit-btn', api.table().container()).on('click', function () {
                    const id = $(this).data('id');
                    const button = this;
                    button.disabled = true;
                    button.querySelector('.spinner-border').classList.remove('d-none');
                    options.onEdit(id)
                        .finally(() => {
                            button.disabled = false;
                            button.querySelector('.spinner-border').classList.add('d-none');
                        });
                });
            }

            if (options.onDelete) {
                $('.delete-btn', api.table().container()).on('click', function () {
                    const id = $(this).data('id');

                    if (options.onDeleteConfirmation) {
                        Swal.fire({
                            title: t('global.confirmation-title'),
                            text: t('global.confirmation-message'),
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: `
            <span class="confirm-text">${t('global.confirmation-yes')}</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          `,
                            cancelButtonText: t('global.confirmation-no'),
                            customClass: {
                                confirmButton: "btn btn-danger me-1",
                                cancelButton: "btn btn-outline-secondary",
                            },
                            buttonsStyling: false,
                            showLoaderOnConfirm: true,
                            preConfirm: () => {
                                return new Promise((resolve, reject) => {
                                    const confirmButton = Swal.getConfirmButton();
                                    const spinnerElement = confirmButton.querySelector('.spinner-border');
                                    const textElement = confirmButton.querySelector('.confirm-text');

                                    // Butonu disable et ve spinner'ı göster
                                    confirmButton.disabled = true;
                                    spinnerElement.classList.remove('d-none');
                                    textElement.classList.add('d-none');

                                    options.onDelete(id)
                                        .then((result) => {
                                            resolve(result);
                                        })
                                        .catch((error) => {
                                            reject(error);
                                        })
                                        .finally(() => {
                                            // İşlem bittiğinde butonu normale döndür
                                            confirmButton.disabled = false;
                                            spinnerElement.classList.add('d-none');
                                            textElement.classList.remove('d-none');
                                        });
                                });
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: t('global.deleted'),
                                    text: t('global.delete-success'),
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: "btn btn-primary",
                                    },
                                    buttonsStyling: false,
                                });
                                api.ajax.reload(); // DataTable'ı yenile
                            }
                        }).catch((error) => {
                            handleAxiosError(error, t('global.delete-error'));
                        });
                    } else if (options.onDelete) {
                        const button = this;
                        button.disabled = true;
                        button.querySelector('.spinner-border').classList.remove('d-none');
                        options.onDelete(id)
                            .finally(() => {
                                button.disabled = false;
                                button.querySelector('.spinner-border').classList.add('d-none');
                            });
                    }
                });
            }
        },
    };
};
