import { useAPI } from './base';

export function useUserAPI() {
  const { apiFetch } = useAPI();

  async function getCurrentUser() {
    return await apiFetch('/v1/users/me', {
      method: 'get',
    });
  }

  async function updateCurrentUser(payload: object) {
    return await apiFetch('/v1/users/me', {
      method: 'put',
      body: payload,
    });
  }

  async function updateCurrentUserPassword(payload: object) {
    return await apiFetch('/v1/users/me/password', {
      method: 'put',
      body: payload,
    });
  }

  async function getUser(id: number) {
    return await apiFetch(`/v1/users/${id}`, {
      method: 'get',
    });
  }

  async function store(payload: object) {
    return await apiFetch('/v1/users', {
      method: 'post',
      body: payload,
    });
  }

  async function update(id: number, payload: object) {
    return await apiFetch(`/v1/users/${id}`, {
      method: 'put',
      body: payload,
    });
  }

  async function destroy(id: number) {
    return await apiFetch(`/v1/users/${id}`, {
      method: 'delete',
    });
  }

  return {
    getCurrentUser,
    updateCurrentUser,
    updateCurrentUserPassword,
    getUser,
    store,
    update,
    destroy,
  };
}
