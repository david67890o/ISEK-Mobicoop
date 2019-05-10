import React from 'react';
import { Edit, SimpleForm, DisabledInput, TextInput, DateInput, BooleanInput, ReferenceInput, SelectInput } from 'react-admin';
import RichTextInput from 'ra-input-rich-text';

const optionRenderer = choice => `${choice.givenName} ${choice.familyName}`;

export const CommunityEdit = (props) => (
    <Edit {...props}>
        <SimpleForm>
            <DisabledInput source="id" label="ID"/>
            <ReferenceInput label="Créateur" source="user" reference="users">
                <SelectInput optionText={optionRenderer} />
            </ReferenceInput>
            <TextInput source="name" label="Nom"/>
            <BooleanInput source="private" label="Privée" />
            <TextInput source="description" label="Description"/>
            <RichTextInput source="fullDescription" label="Description complète" />
            <DateInput disabled source="createdDate" label="Date de création"/>
        </SimpleForm>
    </Edit>
);