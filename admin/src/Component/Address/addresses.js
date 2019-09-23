import React from 'react';
import { 
    Edit,
    SimpleForm, 
    ReferenceInput, SelectInput, TextInput, DisabledInput    
} from 'react-admin';
import GeocompleteInput from '../Utilities/geocomplete';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;

// Edit
export const AddressEdit = (props) => (
    <Edit {...props } title="Adresse > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <ReferenceInput source="user" label="Utilisateur" reference="users">
                <SelectInput optionText={userOptionRenderer} />
            </ReferenceInput>
            <TextInput source="name" label="Nom" />
            <GeocompleteInput />
        </SimpleForm>
    </Edit>
);