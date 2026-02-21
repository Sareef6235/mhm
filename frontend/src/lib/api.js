import axios from 'axios';

export const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL
});

export function setToken(token) {
  if (token) api.defaults.headers.common.Authorization = `Bearer ${token}`;
}
