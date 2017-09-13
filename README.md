# Tasklist GLPI plugin

This plugin was created as a way to automatically generate a list of ticket tasks based on a ticket category

Some parts were adapted from the GLPI Plugin examples.

Basic Usage:
 * Create new ticket categories via the setup -> dropdowns menu
 * Create a new task list via Setup -> Tasklist List Management
 * The name of the newly created tasklist must match an existing category
 * Enable or Disable the task list via the checkbox.
 * Enter the tasks in the text box.  The following format should be used
	Task One
	  Sub task 1
	  Sub task 2
	++
	Task Two
	  Sub task 1
	  Sub task 2
	++
	Task Three
	++
	Task Four
 * The system will parse the text box, and separate tasks at every ++.
 * Create a new ticket, select the category for the ticket, and when the new ticket is 
	submitted, the system will auto-generate tasks.

## Documentation

None yet

## Contributing


