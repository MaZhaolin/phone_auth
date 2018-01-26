import axios from 'axios'

var instance = axios.create({
  baseURL: config.site_url + '/plugin.php?id=phone_auth&action='
})


export default instance;
