let selectedLoanId = null;
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

// Attach event listeners after DOM loads
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".open-loan-modal").forEach((btn) => {
        btn.addEventListener("click", () => {
            let id = btn.dataset.id;
            openLoanModal(id);
        });
    });

    document
        .getElementById("approveLoanBtn")
        .addEventListener("click", approveLoan);

    document
        .getElementById("rejectLoanBtn")
        .addEventListener("click", rejectLoan);
});

function openLoanModal(id) {
    selectedLoanId = id;

    const modal = new bootstrap.Modal(document.getElementById("loanModal"));
    modal.show();

    document.getElementById("loanModalBody").innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border"></div>
        </div>
    `;

    fetch(`/admin/loans/${id}`)
        .then((res) => res.json())
        .then((data) => {
            let eligibility = data.eligible
                ? `<span class="badge bg-success">Éligible</span>`
                : `<span class="badge bg-danger">Non éligible</span>`;

            document.getElementById("loanModalBody").innerHTML = `
                <h5 class="fw-bold">${data.user.name}</h5>
                <p><strong>Téléphone:</strong> ${data.user.phone}</p>
                <p><strong>Montant demandé:</strong> ${data.loan.amount} XOF</p>
                <p><strong>Catégorie:</strong> ${data.loan.category}</p>
                <p><strong>Status:</strong> ${data.loan.status}</p>
                <p><strong>Score Éligibilité:</strong> ${data.score} / 100 ${eligibility}</p>
            `;
        });
}

function approveLoan() {
    fetch(`/admin/loans/${selectedLoanId}/approve`, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrfToken },
    }).then(() => location.reload());
}

function rejectLoan() {
    fetch(`/admin/loans/${selectedLoanId}/reject`, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrfToken },
    }).then(() => location.reload());
}
