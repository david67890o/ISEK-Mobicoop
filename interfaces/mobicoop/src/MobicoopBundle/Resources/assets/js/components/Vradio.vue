<template>
  <section>
    <b-field>
      <b-radio-button v-model="role" name="frequency" native-value="Conducteur" type="is-danger">
        <b-icon icon="close"></b-icon>
        <span>Conducteur</span>
      </b-radio-button>
      <b-radio-button v-model="role" name="role" native-value="Passager" type="is-success">
        <b-icon icon="check"></b-icon>
        <span>Passager</span>
      </b-radio-button>
      <b-radio-button v-model="role" name="role" native-value="both">Passager&Conducteur</b-radio-button>
    </b-field>
    <p class="content">
      <b>Selection:</b>
      {{ role }}
    </p>
  </section>
</template>

<script>
export default {
  data() {
    return {
      role: ""
    };
  }
};
</script>