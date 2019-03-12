<template>
  <section>
    <!-- role -->
    <b-field>
      <b-radio-button v-model="role" name="frequency" native-value="1" type="is-danger">
        <b-icon icon="close"></b-icon>
        <span>Conducteur</span>
      </b-radio-button>
      <b-radio-button v-model="role" name="role" native-value="2" type="is-success">
        <b-icon icon="check"></b-icon>
        <span>Passager</span>
      </b-radio-button>
      <b-radio-button v-model="role" name="role" native-value="3">Passager Conducteur</b-radio-button>
    </b-field>
        <!-- Type -->
    <b-field>
      <b-radio-button v-model="type" name="type" native-value="1" type="is-danger">
        <b-icon icon="close"></b-icon>
        <span>Allez</span>
      </b-radio-button>
      <b-radio-button v-model="type" name="type" native-value="2" type="is-success">
        <b-icon icon="check"></b-icon>
        <span>Allez/Retour</span>
      </b-radio-button>
    </b-field>
    <!-- frequency -->
    <b-field>
      <b-radio-button v-model="frequency" name="frequency" native-value="1" type="is-danger">
        <b-icon icon="close"></b-icon>
        <span>Ponctuel</span>
      </b-radio-button>
      <b-radio-button v-model="frequency" name="frequency" native-value="2" type="is-success">
        <b-icon icon="check"></b-icon>
        <span>Regulier</span>
      </b-radio-button>
    </b-field>
    <!-- Regulier -->
    <div v-if="frequency === '2'" >      
      <div class="columns"v-for="(day,index) in days" :key="index">
        <div class="column">
          <h2 class="title">Aller ({{day}})</h2>
          <b-datepicker
            placeholder="Date de départ..."
            icon="calendar-today">
          </b-datepicker>
          <b-timepicker v-model="timeStart"
            placeholder="Heure de départ...">
            <button class="button is-primary"
              @click="time = new Date()">
              <b-icon icon="clock"></b-icon>
              <span>Maintenant</span>
            </button>
            <button class="button is-danger"
              @click="time = null">
              <b-icon icon="close"></b-icon>
              <span>Effacer</span>
            </button>
          </b-timepicker>
        </div>
        <div class="column" v-if="type === '2'">
          <h2 class="title">Retour ({{day}})</h2>
          <b-datepicker
            placeholder="Date de retour..."
            icon="calendar-today">
          </b-datepicker>
          <b-timepicker v-model="timeReturn"
            placeholder="heure de retour...">
            <button class="button is-primary"
              @click="time = new Date()">
              <b-icon icon="clock"></b-icon>
              <span>Maintenant</span>
            </button>
            <button class="button is-danger"
              @click="time = null">
              <b-icon icon="close"></b-icon>
              <span>Effacer</span>
            </button>
          </b-timepicker>
        </div>
      </div>
    </div>
    <!-- Ponctuel -->
    <div class="columns" v-else>
      <div class="column">
        <h2 class="title">Aller</h2>
        <b-datepicker
          placeholder="Date de départ..."
          icon="calendar-today">
        </b-datepicker>
         <b-timepicker v-model="timeStart"
          placeholder="Heure de départ...">
          <button class="button is-primary"
            @click="time = new Date()">
            <b-icon icon="clock"></b-icon>
            <span>Maintenant</span>
          </button>
          <button class="button is-danger"
            @click="time = null">
            <b-icon icon="close"></b-icon>
            <span>Effacer</span>
          </button>
        </b-timepicker>
      </div>
      <div class="column" v-if="type === '2'">
        <h2 class="title">Retour</h2>
        <b-datepicker
          placeholder="Date de retour..."
          icon="calendar-today">
        </b-datepicker>
        <b-timepicker v-model="timeReturn"
          placeholder="heure de retour...">
          <button class="button is-primary"
            @click="time = new Date()">
            <b-icon icon="clock"></b-icon>
            <span>Maintenant</span>
          </button>
          <button class="button is-danger"
            @click="time = null">
            <b-icon icon="close"></b-icon>
            <span>Effacer</span>
          </button>
        </b-timepicker>
      </div>
    </div>
    <p class="content">
      <b>Selection:</b>
      {{ role }}
    </p>
  </section>
</template>

<script>
export default {
  props:{
    frequency: {
      type: String,
      default: ''
    },
    role: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: ''
    },
    outward: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      timeStart: new Date(),
      timeReturn: new Date(),
      days: ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche']
    };
  },
  mounted(){
    console.log('freq', this.outward);
  }
};
</script>