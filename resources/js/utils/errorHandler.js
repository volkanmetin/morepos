import Swal from 'sweetalert2';

export const handleAxiosError = (error, customTitle = "Error") => {
    console.error('An error occurred:', error);

    let errorMessage = 'An unexpected error occurred.';
    if (error.response && error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
    } else if (error.message) {
        errorMessage = error.message;
    }

    Swal.fire({
        title: customTitle,
        text: errorMessage,
        icon: "error",
        customClass: {
            confirmButton: "btn btn-primary w-xs mt-2",
        },
        buttonsStyling: false,
    });

    throw error;
};

// Axios interceptor for global error handling
import axios from 'axios';

axios.interceptors.response.use(
    response => response,
    error => {
        handleAxiosError(error);
        return Promise.reject(error);
    }
);
