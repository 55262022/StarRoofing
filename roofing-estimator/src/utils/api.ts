import axios from 'axios';

const API_BASE_URL = 'http://your-api-url.com/api'; // Replace with your actual API URL

export const uploadImage = async (imageFile) => {
    try {
        const formData = new FormData();
        formData.append('image', imageFile);

        const response = await axios.post(`${API_BASE_URL}/upload`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        return response.data;
    } catch (error) {
        console.error('Error uploading image:', error);
        throw error;
    }
};

export const fetch3DModel = async (modelId) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/models/${modelId}`);
        return response.data;
    } catch (error) {
        console.error('Error fetching 3D model:', error);
        throw error;
    }
};