import axios from "axios";

export default {
  namespaced: true,
  state: () => ({
    user: null,
    name: localStorage.getItem('user_name') || null,
    roles: JSON.parse(localStorage.getItem('roles') || '[]'),
    permissions: JSON.parse(localStorage.getItem('permissions') || '[]'),
    language: localStorage.getItem('user_lang') || 'en',
    token: localStorage.getItem('auth_token') || null,
  }),

  getters: {
    isAuthenticated: state => !!state.token,
    permissions: (state) => state.permissions,
    hasRole: state => role => state.roles.includes(role),
    hasPermission: state => permission => state.permissions.includes(permission),
    token: state => state.token,
  },

  mutations: {
    SET_USER(state, user) {
      state.user = user;
      state.name = user.name;
      state.roles = user.roles || [];
      state.permissions = user.permissions || [];

      // persist to localStorage
      localStorage.setItem('user_name', user.name);
      localStorage.setItem('roles', JSON.stringify(state.roles));
      localStorage.setItem('permissions', JSON.stringify(state.permissions));
    },

    SET_TOKEN(state, token) {
      state.token = token;
      localStorage.setItem('auth_token', token);
    },

    SET_LANGUAGE(state, language) {
      state.language = language;
      localStorage.setItem('user_lang', language);
    },

    SET_ROLES(state, roles) {
      state.roles = roles;
      localStorage.setItem('roles', JSON.stringify(roles));
    },

    SET_PERMISSIONS(state, permissions) {
      state.permissions = permissions;
      localStorage.setItem('permissions', JSON.stringify(permissions));
    },

    SET_NAME(state, name) {
      state.name = name;
      localStorage.setItem('user_name', name);
    },

    LOGOUT(state) {
      state.user = null;
      state.roles = [];
      state.permissions = [];
      state.token = null;
      state.name = null;

      localStorage.removeItem('user_lang');
      localStorage.removeItem('auth_token');
      localStorage.removeItem('roles');
      localStorage.removeItem('permissions');
      localStorage.removeItem('user_name');
    }
  },

  actions: {
    async fetchUser({ commit, state }) {
      try {
        const response = await axios.get('api/user/profile', {
          headers: {
            Authorization: `Bearer ${state.token}`
          }
        });

        commit('SET_USER', response.data.user);
        commit('SET_NAME', response.data.user.name);
        commit('SET_ROLES', response.data.user.role || []);
        commit('SET_PERMISSIONS', response.data.user.permissions || []);
        commit('SET_LANGUAGE', response.data.user.language || 'en');
      } catch (error) {
        console.error('Error fetching user:', error);
      }
    },

    logout({ commit }) {
      commit('LOGOUT');
    },
    setPermissions({ commit }, permissions) {
        commit("SET_PERMISSIONS", permissions);
    },

    changeLanguage({ commit }, language) {
      commit('SET_LANGUAGE', language); // ✅ fixed
    }
  }
};
