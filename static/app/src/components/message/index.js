import Vue from 'vue'
import Msg from './message.vue'

const MsgBox = Vue.extend(Msg);

let Message = (opts = {}) => {
    let instance = new MsgBox({
        el: document.createElement('div')
    })
    instance.msg = typeof opts === 'string' ? opts : opts.msg;
    document.body.appendChild(instance.$el);
    setTimeout(() => {
        instance.$el.remove();
    }, 1000)
}

export default Message
