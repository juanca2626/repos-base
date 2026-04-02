import axios from 'axios';
import {
  getAccessToken,
  setAccessToken,
  setAccessTokenCognito,
  setUserEmail,
  removeCookiesCross,
  setUserCode,
  setUserId,
  setUserType,
  setUserName,
  setUserClientId,
  setUserRole,
  setUserDepartmentId,
  setUserDepartmentName,
  setUserDepartmentTeamId,
  setUserDepartmentTeamName,
  setUserContract,
} from '../../utils/auth';
import { usePermissionStore } from '@store/permission-store';

const HTTP_OK = 200;
const baseURL = () => `${window.url_back_a2}api`;
const bearerToken = (access_token) => `Bearer ${access_token}`;

async function getRoutes(access_token) {
  setAccessToken(access_token);
  const { data } = await axios.get(`${baseURL()}/menu`, {
    headers: {
      Authorization: bearerToken(access_token),
    },
  });

  return data.data;
}

export async function login_a2({ username, password }) {
  const permissionStore = usePermissionStore();

  //TODO: Solo se modificó para presentación > ELIMINAR
  if (
    username === 'limcro' ||
    username === 'LIMCRO' ||
    username === 'limcac' ||
    username === 'LIMCAC' ||
    username === 'CUSRHL'
  ) {
    const response = await login({ username, password });
    return response;
  }
  //TODO: Solo se modificó para presentación > ELIMINAR

  try {
    const response_a2 = await axios.post(`${window.url_back_a2}api/login`, {
      code: username,
      password: password,
      lang: localStorage.getItem('lang') ?? 'en',
    });

    if (response_a2.status && response_a2.status == HTTP_OK) {
      const { access_token, permissions } = response_a2.data;
      permissionStore.setPermissions(permissions);
      const routes = await getRoutes(access_token);
      permissionStore.setRoutes(routes);

      const response = await login({ username, password });
      return response;
    } else {
      return {
        success: false,
        message: 'unauthorized',
      };
    }
  } catch (e) {
    console.log(e);
    try {
      const response = await login({ username, password });
      return response;
    } catch (e) {
      console.log(e);
      return {
        success: false,
        message: e.response.data?.error || e.response.data?.message || 'unauthorized',
        time: e.response.data?.time || '',
      };
    }
  }
}

async function login({ username, password }) {
  try {
    let response = await axios.post(`${window.url_auth_cognito}auth/login`, {
      username: username,
      password: password,
    });

    if (response.status && response.status == HTTP_OK) {
      const { access_token } = response.data;

      const user_cognito = await axios.get(`${window.url_auth_cognito}auth/me`, {
        headers: {
          Authorization: `Bearer ${access_token}`,
        },
      });

      //TODO: Implementar permisos para GUI y TRP / Routes
      //* Proveedores TRP / GUI de OPE (IFX)
      let redirect = 'home';
      if (['GUI', 'TRP'].includes(user_cognito.data.type)) {
        const { code, contract, type } = user_cognito.data;
        setAccessTokenCognito(access_token);
        setAccessToken(access_token);
        // setUserEmail(email);
        setUserCode(code);
        setUserType(type);
        setUserContract(contract);
        const permissionStore = usePermissionStore();
        //TODO: Implementar PERMISOS para GUI y TRP
        permissionStore.setPermissions([]);

        redirect = 'adventure';
      } else {
        const {
          code,
          email,
          user_type_id,
          department,
          id,
          name,
          client_seller,
          rol,
          photo = null,
        } = user_cognito.data;

        setAccessTokenCognito(access_token);
        setUserEmail(email);
        setUserCode(code);
        setUserType(user_type_id);
        setUserId(id);
        setUserName(name);
        setUserRole(rol);

        localStorage.setItem('photo_user_a3', photo);
        localStorage.setItem(
          'token_socket',
          `${code}-A3-${Date.now().toString(36)}-${Math.random().toString(36).substring(2, 10)}`
        );

        if (department && department.length > 0) {
          setUserDepartmentId(department.id);
          setUserDepartmentName(department.name);
          setUserDepartmentTeamId(department.team.id);
          setUserDepartmentTeamName(department.team.name);
        }

        if (client_seller != null) {
          if (user_type_id == 4) {
            if (client_seller.client_id == '' || client_seller.client_id <= 0) {
              return {
                success: false,
                message: 'unauthorized',
              };
            }
            setUserClientId(client_seller.client_id);
          } else {
            setUserClientId(client_seller.client_id);
          }
        }
      }

      return {
        success: true,
        redirect: redirect,
      };
    }

    return {
      success: false,
      message: 'unauthorized',
    };
  } catch (e) {
    return {
      success: false,
      message: e.response.data?.error || e.response.data?.message || 'unauthorized',
      time: e.response.data?.time || '',
    };
  }
}

export async function logout() {
  let response = true;
  const permissionStore = usePermissionStore();
  try {
    await axios.post(
      `${window.url_back_a2}api/logout`,
      {},
      {
        headers: {
          Authorization: bearerToken(getAccessToken()),
        },
      }
    );
  } catch (e) {
    console.error(e.response.status);
    response = false;
  } finally {
    removeCookiesCross();
    permissionStore.setPermissions([]);
    permissionStore.setRoutes([]);
    response = false;
  }
  return response;
}
