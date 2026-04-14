import axios from "axios";

import { initEcho } from "./echo";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

export const initRealtime = async () => {
	return initEcho();
};
