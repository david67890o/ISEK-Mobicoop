


describe('Share an ad -> Driver -> one way -> ponctual', () => {

  const baseUrl = Cypress.env('CYPRESS_BASEURL');

    it('Visits mobicoop', () => {
        cy.visit(baseUrl)
        cy.contains('Connexion').click()
        cy.url().should('include',baseUrl + 'utilisateur/connexion')
    })

    it('Connexion mobicoop + Share an ad', () => {
    /* Email */
    cy.get('input[id=user_login_form_username]')
    .should('have.attr','placeholder','Saisissez votre adresse email')
    .type('totosmith@email.com')

    /* Password */
    cy.get('input[id=user_login_form_password]')
    .should('have.attr','placeholder','Saisissez votre mot de passe')
    .type('motdepasse')

    cy.get('button[id=user_login_form_login]')
    .click()  
   
    /* Share an ad */
    cy.contains('Partager une annonce')
    .click()
    cy.url().should('include',baseUrl + 'covoiturage/annonce/poster')

    /* Driver */
    cy.get('#Vousêtes0 > .field > :nth-child(1) > .b-radio > :nth-child(2)')
    .click()

    /* Next */
    cy.get('.wizard-btn')
    .click()

    /* One way */
    cy.get('#Trajet2 > .fieldsContainer > :nth-child(1) > .b-radio')
    .contains('Aller')
    .click()
    cy.get('.control > #origin')
    .should('have.attr','placeholder','Depuis')
    .type('Lyon')
    cy.get ('[data-v-12259723]')
    .contains('Lyon')
    .click()

    /* To */
    cy.get('#destination')
    .should('have.attr','placeholder','Vers')
    .type('Strasbourg')
    cy.get ('[data-v-12259723]')
    .contains('Strasbourg')
    .click()

      /* Next */
      cy.get('.wizard-footer-right > span > .wizard-btn')
      .click()

    /* Ponctual */
    cy.get('#Fréquence4 > .fieldsContainer > :nth-child(1) > .b-radio')
    .click()

    
    /* One way - Date */
    cy.get ('.datepicker')

    .click()
    cy.get('.datepicker-body > :nth-child(5) > :nth-child(2)')
    .contains('30')
    .click()

    /* One way - Time */
    cy.get ('.timepicker > .dropdown > .dropdown-trigger > .control > .input')
    .click()
    
    cy.get ('[data-v-31d6c94c]')
    cy.get('.is-mobicoopgreen > :nth-child(2)')
    .click()

    // in order to close the window timepicker
    cy.get('section[class="datepicker-table"]')
    .click({force: true})  

    /* Margin */
    cy.get('select').then(function($select){
        $select.val('5')
      })
      cy.get('select').should('have.value', '5') 

    /* I share my ad */
    cy.get('.wizard-footer-right > span > .wizard-btn')
    .click()
    })
  })


