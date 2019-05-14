import React from 'react';
import { 
    Create, Edit, List, Show, 
    Tab, TabbedShowLayout, 
    Link, 
    Datagrid,
    SimpleForm, 
    DisabledInput, TextInput, DateInput, BooleanInput, ReferenceInput, SelectInput,
    Button, ShowButton, EditButton,
    BooleanField, TextField, DateField, RichTextField, SelectField, ReferenceArrayField, ReferenceField
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;
const userId = `/users/${localStorage.getItem('id')}`;
const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Accepté' },
    { id: 2, name: 'Refusé' },
];

// Create
export const CommunityCreate = (props) => (
    <Create { ...props }>
        <SimpleForm>
            <ReferenceInput label="Créateur" source="user" reference="users" defaultValue={userId}>
                <SelectInput optionText={userOptionRenderer}/>
            </ReferenceInput>
            <TextInput source="name" label="Nom"/>
            <BooleanInput source="private" label="Privée" />
            <TextInput source="description" label="Description"/>
            <RichTextInput source="fullDescription" label="Description complète"/>
        </SimpleForm>
    </Create>
);

// Edit
export const CommunityEdit = (props) => (
    <Edit {...props}>
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <ReferenceInput label="Créateur" source="user" reference="users">
                <SelectInput optionText={userOptionRenderer} />
            </ReferenceInput>
            <TextInput source="name" label="Nom"/>
            <BooleanInput source="private" label="Privée" />
            <TextInput source="description" label="Description"/>
            <RichTextInput source="fullDescription" label="Description complète" />
            <DateInput disabled source="createdDate" label="Date de création"/>
        </SimpleForm>
    </Edit>
);

// List
export const CommunityList = (props) => (
    <List {...props} title="Communities" perPage={ 30 }>
        <Datagrid>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <BooleanField source="private" label="Privée" />
            <TextField source="description" label="Description"/>
            <DateField source="createdDate" label="Date de création"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
const AddNewMemberButton = ({ record }) => (
    <Button
        component={Link}
        to={{
            pathname: `/community_users/create`,
            search: `?community=${record.originId}`
        }}
        label="Ajouter un membre"
    >
    </Button>
);

export const CommunityShow = (props) => (
    <Show { ...props }>
        <TabbedShowLayout>
            <Tab label="Détails">
                <TextField source="originId" label="ID"/>
                <TextField source="name" label="Nom"/>
                <TextField source="description" label="Description"/>
                <RichTextField source="fullDescription" label="Description complète"/>
                <DateField source="createdDate" label="Date de création"/>
                <EditButton />
            </Tab>
            <Tab label="Membres" path="members">
                <ReferenceArrayField reference="community_users" source="communityUsers" addLabel={false}>
                    <Datagrid>
                        <ReferenceField label="Prénom" source="user" reference="users" linkType="">
                            <TextField source="givenName" />
                        </ReferenceField>
                        <ReferenceField label="Nom" source="user" reference="users" linkType="">
                            <TextField source="familyName" />
                        </ReferenceField>
                        <SelectField label="Status" source="status" choices={statusChoices} />
                        <EditButton />
                    </Datagrid>
                </ReferenceArrayField>
                <AddNewMemberButton />
            </Tab>
        </TabbedShowLayout>
    </Show>
);