![VM Checkout Icon](resources/img/icon.png "VM Checkout")
# Alfred Virtual Machine Checkout

A Workflow that adds TSM-vm-checkout functionality to the Alfred 2 application.

## Download

[VirtualMachineCheckout.alfredworkflow](resources/exports/VirtualMachineCheckout.alfredworkflow)

## Quick Start
1. [__Set__](#set-name--reset-name) - `set` to set your VM checkout name
2. [__Claim__](#claim) - `claim` to search all avaliable vm which can be claimed  ([__bonus__](#claim-cmd))
3. [__Vacate__](#vacate) - `vacate` to search all personally claimed vms

## Functions

### VM
Shows the all VM checkout workflow actions. 
### Set Name / Reset Name
Allow the user to either set or reset their VM checkout name that is used to claims vms. This creates a simple txt document containing the user provided name.
### Claim 
Search the server for available vms.  Selecting a result sends a request to the server which claims the machine and the user in notified that they now own that vm. 
#### Claim (cmd)
Holding down `cmd` will open Remote Desktop Connection Application and the claimed VM name will be pasted into the prompt.
### Vacate 
Search the server for claimed vms that have a username which match the user definded checkout name providing when running `set`.  Selecting a result sends a request to the server to vacate the vm.
If you have more than one vm checked out, a 'Vacate All VMs' result will be shown that allows you to vacate all your claimed vm at once. *great for when you've got a couple vms claimed and you're signing off for the day*

#### Things To Do:
- See who has what - check to see who has a vm that you want.
- 'Claim/Vacate' if no name is set request one (already available), launch intend alfred function claim or vacate.
- &#x2713; Open Remote Desktop Connection when claiming a VM
- &#x2713; Copy VM name to clipboard for easy pasting into the RDC
- 'VM' & 'Vacate' - provide result to take user to the VM Checkout page for manual administrating 

#### Known Issues
Regardless of what action the user ques in Alfred, if no vm checkout name has been set the user will be prompted to set one first. This confilcts with the ability launch RDC when claiming a machine.  I was trying to avoid this but: Possible solution is to notfiy the user with an error message (for ex: "Your VM checkout name is not set, please run 'Set Name'").  RDC will still open when a name has been set.  
