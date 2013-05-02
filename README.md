TSM-alfred-vm-checkout
======================

Workflow that adds TSM-vm-checkout functionality to the Alfred 2 application.

## Functions

### VM
Shows the avaiable VM checkout workflow actions. 
### Set Name / Reset Name
Allow the user to either set or reset a VM checkout name that will be used to claims vms. This sets a simple txt document containing the user provided name.
### Claim 
Search the server for only available vms.  Selecting a result sends a request to the server which claims the machine and the user in notified that they now own that vm. 
#### Claim (cmd)
By holding down `cmd` Remote Desktop Connection will be open.  The name of the vm will be copied to the users clipboard and pasted into the RDC claim
### Vacate 
Search the server for claim vms that match the user name of the VM checkout name providing by running 'Set Name'.  Selecting a result sends a request to the server to vacate the vm and the user is notified that it has been vacated. 
If you have more than one vm checked out, a 'Vacate All VMs' result will be shown that allows you to vacate all your claimed vm at once. 


#### Known Issues
Regardless of what action the user ques in Alfred, if no vm checkout name has been set the user will be prompted to set one first. This confilcts with the ability launch RDC when claiming a machine.  I was trying to avoid this but: Possible solution is to notfiy the user with an error message (for ex: "Your VM checkout name is not set, please run 'Set Name'").  RDC will still open when a name has been set.  
